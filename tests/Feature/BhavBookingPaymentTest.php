<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\BhavBookingPayment;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BhavBookingPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(): Customer
    {
        return Customer::create([
            'customer_no' => 'CUST-'.rand(1000, 9999),
            'name' => 'Payment Test Party',
            'mobile' => '98'.rand(10000000, 99999999),
            'party_type' => 'CUSTOMER',
            'city' => 'Indore',
        ]);
    }

    public function test_sell_booking_with_cash_only_payment(): void
    {
        $customer = $this->createCustomer();

        $payload = [
            'customer_id' => $customer->id,
            'request_token' => Str::uuid()->toString(),
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['weight' => 10.000, 'rate' => 70000.00],
            ],
            'payments' => [
                ['mode' => 'CASH', 'amount' => 15000.00],
            ],
        ];

        $response = $this->postJson('/bhav-bookings', $payload);
        $response->assertStatus(201);

        $bookingId = $response->json('id');
        $this->assertDatabaseHas('bhav_bookings', [
            'id' => $bookingId,
            'trade_type' => 'SELL',
            'total_amount' => 70000.00,
            'received_amount' => 15000.00,
            'given_amount' => 0.00,
        ]);
    }

    public function test_sell_booking_with_cash_and_online_payments(): void
    {
        $customer = $this->createCustomer();

        $payload = [
            'customer_id' => $customer->id,
            'request_token' => Str::uuid()->toString(),
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['weight' => 10.000, 'rate' => 70000.00], // 70,000
            ],
            'payments' => [
                ['mode' => 'CASH', 'amount' => 20000.00],
                ['mode' => 'ONLINE', 'online_method' => 'UPI', 'reference_no' => 'UPI123456', 'amount' => 30000.00],
            ],
        ];

        $response = $this->postJson('/bhav-bookings', $payload);
        $response->assertStatus(201);

        $bookingId = $response->json('id');
        $this->assertDatabaseHas('bhav_bookings', [
            'id' => $bookingId,
            'trade_type' => 'SELL',
            'total_amount' => 70000.00,
            'received_amount' => 50000.00,
            'given_amount' => 0.00,
        ]);

        $this->assertDatabaseHas('bhav_booking_payments', [
            'bhav_booking_id' => $bookingId,
            'mode' => 'CASH',
            'amount' => 20000.00,
            'source' => 'BOOKING',
        ]);

        $this->assertDatabaseHas('bhav_booking_payments', [
            'bhav_booking_id' => $bookingId,
            'mode' => 'ONLINE',
            'online_method' => 'UPI',
            'reference_no' => 'UPI123456',
            'amount' => 30000.00,
            'source' => 'BOOKING',
        ]);
    }

    public function test_buy_booking_payments_add_to_given_amount(): void
    {
        $customer = $this->createCustomer();

        $payload = [
            'customer_id' => $customer->id,
            'request_token' => Str::uuid()->toString(),
            'trade_type' => 'BUY',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['weight' => 10.000, 'rate' => 70000.00], // 70,000
            ],
            'payments' => [
                ['mode' => 'CASH', 'amount' => 40000.00],
            ],
        ];

        $response = $this->postJson('/bhav-bookings', $payload);
        $response->assertStatus(201);

        $bookingId = $response->json('id');
        $this->assertDatabaseHas('bhav_bookings', [
            'id' => $bookingId,
            'trade_type' => 'BUY',
            'total_amount' => 70000.00,
            'given_amount' => 40000.00,
            'received_amount' => 0.00,
        ]);
    }

    public function test_payment_exceeding_total_amount_is_rejected(): void
    {
        $customer = $this->createCustomer();

        $payload = [
            'customer_id' => $customer->id,
            'request_token' => Str::uuid()->toString(),
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['weight' => 10.000, 'rate' => 70000.00], // 70,000
            ],
            'payments' => [
                ['mode' => 'CASH', 'amount' => 75000.00], // > 70,000
            ],
        ];

        $response = $this->postJson('/bhav-bookings', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payments']);
    }

    public function test_booking_payments_do_not_block_edit_or_delete(): void
    {
        $customer = $this->createCustomer();

        $booking = BhavBooking::create([
            'booking_no' => 'BB-'.rand(100000, 999999),
            'customer_id' => $customer->id,
            'request_token' => Str::uuid()->toString(),
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'status' => 'PENDING',
            'total_weight' => 10.000,
            'delivered_weight' => 0.000,
            'total_amount' => 70000.00,
            'received_amount' => 25000.00,
            'given_amount' => 0.00,
            'booked_at' => now(),
        ]);

        BhavBookingPayment::create([
            'bhav_booking_id' => $booking->id,
            'mode' => 'CASH',
            'amount' => 25000.00,
            'source' => 'BOOKING',
            'sort_order' => 1,
            'paid_at' => now(),
        ]);

        // Show endpoint includes has_external_payments = false
        $showResponse = $this->getJson("/bhav-bookings/{$booking->id}");
        $showResponse->assertStatus(200);
        $this->assertFalse($showResponse->json('has_external_payments'));
        $this->assertCount(1, $showResponse->json('payments'));

        // Update works and replaces booking payment
        $updatePayload = [
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['weight' => 10.000, 'rate' => 70000.00],
            ],
            'payments' => [
                ['mode' => 'CASH', 'amount' => 35000.00],
            ],
        ];

        $updateResponse = $this->putJson("/bhav-bookings/{$booking->id}", $updatePayload);
        $updateResponse->assertStatus(200);

        $booking->refresh();
        $this->assertEquals(35000.00, $booking->received_amount);

        // Delete works
        $deleteResponse = $this->deleteJson("/bhav-bookings/{$booking->id}");
        $deleteResponse->assertStatus(200);
        $this->assertSoftDeleted('bhav_bookings', ['id' => $booking->id]);
    }

    public function test_external_buy_sell_payments_block_edit_and_delete(): void
    {
        $customer = $this->createCustomer();

        $booking = BhavBooking::create([
            'booking_no' => 'BB-'.rand(100000, 999999),
            'customer_id' => $customer->id,
            'request_token' => Str::uuid()->toString(),
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'status' => 'PENDING',
            'total_weight' => 10.000,
            'delivered_weight' => 0.000,
            'total_amount' => 70000.00,
            'received_amount' => 10000.00,
            'given_amount' => 0.00,
            'booked_at' => now(),
        ]);

        BhavBookingPayment::create([
            'bhav_booking_id' => $booking->id,
            'mode' => 'CASH',
            'amount' => 10000.00,
            'source' => 'BUY_SELL', // External payment
            'sort_order' => 1,
            'paid_at' => now(),
        ]);

        $showResponse = $this->getJson("/bhav-bookings/{$booking->id}");
        $showResponse->assertStatus(200);
        $this->assertTrue($showResponse->json('has_external_payments'));

        // Update should be rejected with 422
        $updateResponse = $this->putJson("/bhav-bookings/{$booking->id}", [
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['weight' => 10.000, 'rate' => 70000.00],
            ],
        ]);
        $updateResponse->assertStatus(422);

        // Delete should be rejected with 422
        $deleteResponse = $this->deleteJson("/bhav-bookings/{$booking->id}");
        $deleteResponse->assertStatus(422);
    }

    /**
     * Test exact browser payload for SELL Silver 1.000 gm x 1800 (Rs 180) with 3 CASH payments of Rs 1 each.
     * Must return 201 and save 3 payments with received_amount = 3.00.
     */
    public function test_sell_silver_with_three_cash_payments_exact_browser_payload(): void
    {
        $customer = $this->createCustomer();

        $payload = [
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'SILVER',
            'items' => [
                ['weight' => 1.000, 'rate' => 1800.00],
            ],
            'payments' => [
                ['mode' => 'CASH', 'online_method' => null, 'amount' => 1.00],
                ['mode' => 'CASH', 'online_method' => null, 'amount' => 1.00],
                ['mode' => 'CASH', 'online_method' => null, 'amount' => 1.00],
            ],
            'narration' => null,
            'send_whatsapp' => false,
            'customer_id' => $customer->id,
            'request_token' => Str::uuid()->toString(),
        ];

        $response = $this->postJson('/bhav-bookings', $payload);
        $response->assertStatus(201);

        $bookingId = $response->json('id');
        $this->assertDatabaseHas('bhav_bookings', [
            'id' => $bookingId,
            'customer_id' => $customer->id,
            'trade_type' => 'SELL',
            'metal' => 'SILVER',
            'total_weight' => 1.000,
            'total_amount' => 180.00,
            'received_amount' => 3.00,
            'given_amount' => 0.00,
        ]);

        $this->assertDatabaseCount('bhav_booking_payments', 3);
        $this->assertEquals(
            3,
            BhavBookingPayment::where('bhav_booking_id', $bookingId)
                ->where('mode', 'CASH')
                ->where('amount', 1.00)
                ->whereNull('reference_no')
                ->count()
        );
    }
}
