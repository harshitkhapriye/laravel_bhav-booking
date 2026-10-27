<?php

namespace App\Services;

use App\Models\BhavBooking;
use App\Models\BhavBookingLog;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
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
     * @return array{items: array, total_weight: float, total_amount: float}
     */
    public function calculateItems(array $items, ?string $defaultMetal = null): array
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

            $metal = $itemData['metal'] ?? $defaultMetal;

            $calculatedItems[] = [
                'sort_order' => $index + 1,
                'metal' => $metal,
                'weight' => $weight,
                'rate' => $rate,
                'amount' => $rowAmount,
            ];
        }

        // Round grand totals to proper bullion decimals
        $totalWeight = round($totalWeight, 3);
        $totalAmount = round($totalAmount, 2);

        return [
            'items' => $calculatedItems,
            'total_weight' => $totalWeight,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Check whether a booking can be edited or deleted.
     * Allowed only if status = PENDING and delivered_weight = 0 and NO payments with source BUY_SELL.
     * Payments made at booking time (source BOOKING) do NOT block edit/delete.
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
     * Create new Bhav Bookings.
     * Groups items by metal, creating one booking per metal inside a single DB transaction.
     * Linked together via booking_group_id.
     *
     * @return array{bookings: array<BhavBooking>, group_id: string, created: bool, booking: ?BhavBooking}
     */
    public function create(array $validated): array
    {
        $groupId = $validated['request_token'];

        // 1. DUPLICATE PROTECTION (Idempotency)
        // If bookings with booking_group_id = request_token already exist, return them (200)
        $existing = BhavBooking::where('booking_group_id', $groupId)
            ->orWhere('request_token', $groupId)
            ->with(['items', 'customer', 'payments'])
            ->orderBy('id')
            ->get();

        if ($existing->isNotEmpty()) {
            return [
                'bookings' => $existing->all(),
                'group_id' => $existing->first()->booking_group_id ?? $groupId,
                'created' => false,
                'booking' => $existing->first(),
            ];
        }

        // 2. GROUP ITEMS BY METAL
        $itemsByMetal = [];
        foreach ($validated['items'] as $item) {
            $metal = $item['metal'] ?? ($validated['metal'] ?? 'GOLD');
            $itemsByMetal[$metal][] = $item;
        }

        // Canonical order: GOLD first, then SILVER
        $sortedMetals = [];
        foreach (['GOLD', 'SILVER'] as $m) {
            if (! empty($itemsByMetal[$m])) {
                $sortedMetals[] = $m;
            }
        }
        foreach (array_keys($itemsByMetal) as $m) {
            if (! in_array($m, $sortedMetals, true)) {
                $sortedMetals[] = $m;
            }
        }

        // 3. SERVER-SIDE RECALCULATION PER METAL
        $metalCalculated = [];
        $combinedTotalAmount = 0.0;

        foreach ($sortedMetals as $metal) {
            $calc = $this->calculateItems($itemsByMetal[$metal], $metal);
            $metalCalculated[$metal] = $calc;
            $combinedTotalAmount += $calc['total_amount'];
        }
        $combinedTotalAmount = round($combinedTotalAmount, 2);

        // 4. PAYMENT TOTAL VALIDATION
        // Check that sum of payments does not exceed combined booking total amount
        $paymentsData = $validated['payments'] ?? [];
        $totalPayments = 0.0;
        foreach ($paymentsData as $p) {
            $totalPayments += (float) $p['amount'];
        }
        $totalPayments = round($totalPayments, 2);

        if ($totalPayments > $combinedTotalAmount) {
            throw ValidationException::withMessages([
                'payments' => ['Payment cannot be more than booking amount ₹'.indian_money($combinedTotalAmount)],
            ]);
        }

        // 5. ALLOCATE PAYMENTS ACROSS METALS
        // Entry order: fill GOLD booking first, then SILVER. Split if boundary crossed.
        $allocatedPayments = [];
        foreach ($sortedMetals as $m) {
            $allocatedPayments[$m] = [];
        }

        $remainingCapacity = [];
        foreach ($sortedMetals as $m) {
            $remainingCapacity[$m] = $metalCalculated[$m]['total_amount'];
        }

        foreach ($paymentsData as $payment) {
            $pAmount = round((float) $payment['amount'], 2);
            if ($pAmount <= 0) {
                continue;
            }

            foreach ($sortedMetals as $m) {
                if ($remainingCapacity[$m] <= 0) {
                    continue;
                }

                $take = min($pAmount, $remainingCapacity[$m]);
                $take = round($take, 2);

                if ($take > 0) {
                    $splitPayment = $payment;
                    $splitPayment['amount'] = $take;
                    $allocatedPayments[$m][] = $splitPayment;

                    $remainingCapacity[$m] = round($remainingCapacity[$m] - $take, 2);
                    $pAmount = round($pAmount - $take, 2);
                }

                if ($pAmount <= 0) {
                    break;
                }
            }
        }

        // 6. DATABASE TRANSACTION WITH RETRY
        $maxAttempts = 3;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;
            try {
                $createdBookings = DB::transaction(function () use ($validated, $sortedMetals, $metalCalculated, $allocatedPayments, $groupId) {
                    $bookings = [];
                    $now = now();
                    $isSell = $validated['trade_type'] === 'SELL';

                    foreach ($sortedMetals as $metal) {
                        $calc = $metalCalculated[$metal];
                        $metalPayments = $allocatedPayments[$metal] ?? [];
                        $paidForBooking = round((float) collect($metalPayments)->sum('amount'), 2);

                        $receivedAmount = $isSell ? $paidForBooking : 0.00;
                        $givenAmount = $isSell ? 0.00 : $paidForBooking;

                        $bookingNo = BhavBooking::nextBookingNo(true);

                        $newBooking = BhavBooking::create([
                            'booking_no' => $bookingNo,
                            'booking_group_id' => $groupId,
                            'customer_id' => $validated['customer_id'],
                            'trade_type' => $validated['trade_type'],
                            'entry_type' => $validated['entry_type'],
                            'metal' => $metal,
                            'total_weight' => $calc['total_weight'],
                            'total_amount' => $calc['total_amount'],
                            'delivered_weight' => 0.000,
                            'given_amount' => $givenAmount,
                            'received_amount' => $receivedAmount,
                            'status' => 'PENDING',
                            'narration' => $validated['narration'] ?? null,
                            'send_whatsapp' => $validated['send_whatsapp'] ?? false,
                            'request_token' => count($sortedMetals) === 1 ? $groupId : (string) \Illuminate\Support\Str::uuid(),
                            'booked_at' => $now,
                        ]);

                        // Save line items
                        $newBooking->items()->createMany($calc['items']);

                        // Save allocated payments with source = 'BOOKING'
                        if (! empty($metalPayments)) {
                            $paymentRows = [];
                            foreach ($metalPayments as $index => $payment) {
                                $paymentRows[] = [
                                    'mode' => $payment['mode'],
                                    'online_method' => $payment['mode'] === 'ONLINE' ? ($payment['online_method'] ?? 'UPI') : null,
                                    'reference_no' => $payment['reference_no'] ?? null,
                                    'amount' => round((float) $payment['amount'], 2),
                                    'source' => 'BOOKING',
                                    'sort_order' => $index + 1,
                                    'paid_at' => $now,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                            }
                            $newBooking->payments()->createMany($paymentRows);
                        }

                        // Activity log: booking created
                        BhavBookingLog::create([
                            'bhav_booking_id' => $newBooking->id,
                            'action' => 'CREATED',
                            'old_values' => null,
                            'new_values' => [
                                'booking_no' => $newBooking->booking_no,
                                'booking_group_id' => $groupId,
                                'trade_type' => $newBooking->trade_type,
                                'metal' => $newBooking->metal,
                                'total_weight' => (float) $newBooking->total_weight,
                                'total_amount' => (float) $newBooking->total_amount,
                                'given_amount' => (float) $newBooking->given_amount,
                                'received_amount' => (float) $newBooking->received_amount,
                                'payments_count' => count($metalPayments),
                            ],
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'created_at' => $now,
                        ]);

                        $bookings[] = $newBooking;
                    }

                    return $bookings;
                });

                foreach ($createdBookings as $booking) {
                    $booking->load(['items', 'customer', 'payments']);
                }

                return [
                    'bookings' => $createdBookings,
                    'group_id' => $groupId,
                    'created' => true,
                    'booking' => $createdBookings[0] ?? null,
                ];
            } catch (UniqueConstraintViolationException|QueryException $e) {
                // If inserting fails because booking_group_id / request_token already exists, return existing bookings
                $existing = BhavBooking::where('booking_group_id', $groupId)
                    ->orWhere('request_token', $groupId)
                    ->with(['items', 'customer', 'payments'])
                    ->orderBy('id')
                    ->get();

                if ($existing->isNotEmpty()) {
                    return [
                        'bookings' => $existing->all(),
                        'group_id' => $existing->first()->booking_group_id ?? $groupId,
                        'created' => false,
                        'booking' => $existing->first(),
                    ];
                }

                // On unique violation of booking_no, retry generation up to 3 times
                if ($attempt >= $maxAttempts) {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Failed to create booking after maximum retries');
    }

    /**
     * Update an existing Bhav Booking.
     * Recalculates totals and atomically replaces line items and BOOKING payments inside a DB transaction.
     * Never touches BUY_SELL source payments.
     */
    public function update(BhavBooking $booking, array $validated): BhavBooking
    {
        // Bug e: Check canModify with SOP message
        if (! $this->canModify($booking)) {
            throw ValidationException::withMessages([
                'booking' => ['This booking has delivery/payment and cannot be edited'],
            ]);
        }

        // 1. RECALCULATE ON SERVER
        $calculated = $this->calculateItems($validated['items'], $validated['metal']);

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
                'payments' => ['Payment cannot be more than booking amount ₹'.indian_money($calculated['total_amount'])],
            ]);
        }

        // Snapshot old values before update
        $oldValues = [
            'booking_no' => $booking->booking_no,
            'trade_type' => $booking->trade_type,
            'metal' => $booking->metal,
            'total_weight' => (float) $booking->total_weight,
            'total_amount' => (float) $booking->total_amount,
            'given_amount' => (float) $booking->given_amount,
            'received_amount' => (float) $booking->received_amount,
            'narration' => $booking->narration,
            'payments_count' => $booking->payments()->count(),
        ];

        // Recalculate given_amount / received_amount based on current trade_type
        $isSell = $validated['trade_type'] === 'SELL';
        $receivedAmount = $isSell ? $totalPayments : 0.00;
        $givenAmount = $isSell ? 0.00 : $totalPayments;

        // 3. ATOMIC DATABASE TRANSACTION
        DB::transaction(function () use ($booking, $validated, $calculated, $paymentsData, $givenAmount, $receivedAmount, $oldValues) {
            $booking->update([
                // customer_id is immutable in edit mode: always retain original value from database
                'trade_type' => $validated['trade_type'],
                'entry_type' => $validated['entry_type'],
                'metal' => $validated['metal'],
                'total_weight' => $calculated['total_weight'],
                'total_amount' => $calculated['total_amount'],
                'given_amount' => $givenAmount,
                'received_amount' => $receivedAmount,
                'narration' => $validated['narration'] ?? null,
                'send_whatsapp' => $validated['send_whatsapp'] ?? false,
            ]);

            // Delete old items and insert fresh calculated items
            $booking->items()->delete();
            $booking->items()->createMany($calculated['items']);

            // Replace only the payments with source BOOKING; never touch source BUY_SELL payments
            $booking->payments()->where('source', 'BOOKING')->delete();

            if (! empty($paymentsData)) {
                $now = now();
                $paymentRows = [];
                foreach ($paymentsData as $index => $payment) {
                    $paymentRows[] = [
                        'mode' => $payment['mode'],
                        'online_method' => $payment['mode'] === 'ONLINE' ? ($payment['online_method'] ?? 'UPI') : null,
                        'reference_no' => $payment['reference_no'] ?? null,
                        'amount' => round((float) $payment['amount'], 2),
                        'source' => 'BOOKING',
                        'sort_order' => $index + 1,
                        'paid_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                $booking->payments()->createMany($paymentRows);
            }

            // Activity log: booking updated
            BhavBookingLog::create([
                'bhav_booking_id' => $booking->id,
                'action' => 'UPDATED',
                'old_values' => $oldValues,
                'new_values' => [
                    'booking_no' => $booking->booking_no,
                    'trade_type' => $validated['trade_type'],
                    'metal' => $validated['metal'],
                    'total_weight' => $calculated['total_weight'],
                    'total_amount' => $calculated['total_amount'],
                    'given_amount' => $givenAmount,
                    'received_amount' => $receivedAmount,
                    'narration' => $validated['narration'] ?? null,
                    'payments_count' => count($paymentsData),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        });

        $booking->load(['items', 'customer', 'payments']);

        return $booking;
    }

    /**
     * Soft delete an existing Bhav Booking.
     */
    public function delete(BhavBooking $booking, bool $silent = false): bool
    {
        // Bug e: Check canModify with SOP message
        if (! $this->canModify($booking)) {
            throw ValidationException::withMessages([
                'booking' => ['This booking has delivery/payment and cannot be edited'],
            ]);
        }

        $oldValues = [
            'booking_no' => $booking->booking_no,
            'total_weight' => (float) $booking->total_weight,
            'total_amount' => (float) $booking->total_amount,
        ];

        BhavBookingLog::create([
            'bhav_booking_id' => $booking->id,
            'action' => $silent ? 'SILENT_DELETED' : 'DELETED',
            'old_values' => $oldValues,
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return (bool) $booking->delete();
    }

    /**
     * Log an external action on a booking (e.g. PRINTED, SHARED).
     */
    public function logAction(BhavBooking $booking, string $action, ?array $meta = null): BhavBookingLog
    {
        return BhavBookingLog::create([
            'bhav_booking_id' => $booking->id,
            'action' => strtoupper($action),
            'old_values' => null,
            'new_values' => $meta,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
