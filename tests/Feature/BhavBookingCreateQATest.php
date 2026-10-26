<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BhavBookingCreateQATest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(): Customer
    {
        return Customer::create([
            'customer_no' => 'CUST-0001',
            'name'        => 'Test Customer',
            'mobile'      => '9826011122',
            'party_type'  => 'CUSTOMER',
            'city'        => 'Indore',
        ]);
    }

    public function test_create_single_row_booking_calculates_correct_totals(): void
    {
        $customer = $this->createCustomer();

        // 10gm at 70,000/10gm = 70,000
        $response = $this->postJson('/bhav-bookings', [
            'request_token' => Str::uuid()->toString(),
            'customer_id'   => $customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [
                ['weight' => 10.000, 'rate' => 70000.00],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'total_weight' => '10.000',
            'total_amount' => '70000.00',
            'status'       => 'PENDING',
        ]);
    }

    public function test_create_multi_rows_booking_2gm_plus_3gm_at_148000_rate_per_10gm_equals_74000(): void
    {
        $customer = $this->createCustomer();

        // 2gm * 148,000 / 10 = 29,600
        // 3gm * 148,000 / 10 = 44,400
        // Total = 74,000
        $response = $this->postJson('/bhav-bookings', [
            'request_token' => Str::uuid()->toString(),
            'customer_id'   => $customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [
                ['weight' => 2.000, 'rate' => 148000.00],
                ['weight' => 3.000, 'rate' => 148000.00],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'total_weight' => '5.000',
            'total_amount' => '74000.00',
        ]);

        $data = $response->json();
        $this->assertCount(2, $data['items']);
        $this->assertEquals('29600.00', $data['items'][0]['amount']);
        $this->assertEquals('44400.00', $data['items'][1]['amount']);
    }

    public function test_create_booking_with_fractional_weight_decimals_2_125_plus_0_875_equals_3_000(): void
    {
        $customer = $this->createCustomer();

        $response = $this->postJson('/bhav-bookings', [
            'request_token' => Str::uuid()->toString(),
            'customer_id'   => $customer->id,
            'trade_type'    => 'BUY',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [
                ['weight' => 2.125, 'rate' => 70000.00],
                ['weight' => 0.875, 'rate' => 70000.00],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'total_weight' => '3.000',
            'total_amount' => '21000.00',
        ]);
    }

    public function test_server_ignores_amounts_sent_by_browser_and_recalculates(): void
    {
        $customer = $this->createCustomer();

        // Browser attempts to send fake amount: 10gm at 70,000/10gm, but claims amount is 1.00
        $response = $this->postJson('/bhav-bookings', [
            'request_token' => Str::uuid()->toString(),
            'customer_id'   => $customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'total_amount'  => 1.00, // Fake total amount sent by client
            'items'         => [
                ['weight' => 10.000, 'rate' => 70000.00, 'amount' => 1.00], // Fake item amount
            ],
        ]);

        $response->assertStatus(201);
        // Server must ignore client amounts and recalculate true 70,000
        $this->assertEquals('70000.00', $response->json('total_amount'));
        $this->assertEquals('70000.00', $response->json('items.0.amount'));
    }

    public function test_create_booking_rejects_zero_negative_blank_too_large_weight_and_rate(): void
    {
        $customer = $this->createCustomer();

        // 1. Zero weight
        $res1 = $this->postJson('/bhav-bookings', [
            'request_token' => Str::uuid()->toString(),
            'customer_id'   => $customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [
                ['weight' => 0, 'rate' => 70000.00],
            ],
        ]);
        $res1->assertStatus(422);
        $res1->assertJsonValidationErrors(['items.0.weight']);

        // 2. Negative rate
        $res2 = $this->postJson('/bhav-bookings', [
            'request_token' => Str::uuid()->toString(),
            'customer_id'   => $customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [
                ['weight' => 10.000, 'rate' => -500],
            ],
        ]);
        $res2->assertStatus(422);
        $res2->assertJsonValidationErrors(['items.0.rate']);

        // 3. Blank weight
        $res3 = $this->postJson('/bhav-bookings', [
            'request_token' => Str::uuid()->toString(),
            'customer_id'   => $customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [
                ['weight' => '', 'rate' => 70000.00],
            ],
        ]);
        $res3->assertStatus(422);
        $res3->assertJsonValidationErrors(['items.0.weight']);

        // 4. Too large weight (> 99999.999)
        $res4 = $this->postJson('/bhav-bookings', [
            'request_token' => Str::uuid()->toString(),
            'customer_id'   => $customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [
                ['weight' => 1000000.000, 'rate' => 70000.00],
            ],
        ]);
        $res4->assertStatus(422);
        $res4->assertJsonValidationErrors(['items.0.weight']);
    }

    public function test_create_booking_requires_customer(): void
    {
        $response = $this->postJson('/bhav-bookings', [
            'request_token' => Str::uuid()->toString(),
            'customer_id'   => null,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [
                ['weight' => 10.000, 'rate' => 70000.00],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['customer_id']);
    }

    public function test_create_booking_limits_to_max_50_items(): void
    {
        $customer = $this->createCustomer();

        $items = [];
        for ($i = 0; $i < 51; $i++) {
            $items[] = ['weight' => 1.000, 'rate' => 70000.00];
        }

        $response = $this->postJson('/bhav-bookings', [
            'request_token' => Str::uuid()->toString(),
            'customer_id'   => $customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => $items,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
    }

    public function test_idempotency_same_request_token_twice_returns_same_booking_with_200(): void
    {
        $customer = $this->createCustomer();
        $token = Str::uuid()->toString();

        $payload = [
            'request_token' => $token,
            'customer_id'   => $customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [
                ['weight' => 10.000, 'rate' => 70000.00],
            ],
        ];

        // 1st request -> 201 Created
        $res1 = $this->postJson('/bhav-bookings', $payload);
        $res1->assertStatus(201);
        $id1 = $res1->json('id');

        // 2nd request with identical token -> 200 OK, returns same booking
        $res2 = $this->postJson('/bhav-bookings', $payload);
        $res2->assertStatus(200);
        $this->assertEquals($id1, $res2->json('id'));

        // Verify only 1 record created in database
        $this->assertEquals(1, BhavBooking::where('request_token', $token)->count());
    }

    public function test_booking_no_sequential_generation(): void
    {
        $customer = $this->createCustomer();

        $res1 = $this->postJson('/bhav-bookings', [
            'request_token' => Str::uuid()->toString(),
            'customer_id'   => $customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [['weight' => 1.000, 'rate' => 70000.00]],
        ]);
        $res1->assertStatus(201);
        $this->assertEquals('BB-000001', $res1->json('booking_no'));

        $res2 = $this->postJson('/bhav-bookings', [
            'request_token' => Str::uuid()->toString(),
            'customer_id'   => $customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [['weight' => 1.000, 'rate' => 70000.00]],
        ]);
        $res2->assertStatus(201);
        $this->assertEquals('BB-000002', $res2->json('booking_no'));
    }

    public function test_booking_no_race_retry_on_unique_collision(): void
    {
        // Bug b: On unique violation of booking_no, retry generation up to 3 times
        $customer = $this->createCustomer();

        // Create BB-000001 manually
        BhavBooking::create([
            'booking_no'       => 'BB-000001',
            'customer_id'      => $customer->id,
            'request_token'    => Str::uuid()->toString(),
            'trade_type'       => 'SELL',
            'entry_type'       => 'ONLY_ENTRY',
            'metal'            => 'GOLD',
            'status'           => 'PENDING',
            'total_weight'     => 10.000,
            'delivered_weight' => 0.000,
            'total_amount'     => 70000.00,
            'received_amount'  => 0.00,
            'given_amount'     => 0.00,
            'booked_at'        => now(),
        ]);

        $response = $this->postJson('/bhav-bookings', [
            'request_token' => Str::uuid()->toString(),
            'customer_id'   => $customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [['weight' => 1.000, 'rate' => 70000.00]],
        ]);

        $response->assertStatus(201);
        $this->assertEquals('BB-000002', $response->json('booking_no'));
    }

    public function test_idempotency_race_on_duplicate_token_returns_200(): void
    {
        // Bug c: If inserting fails because request_token already exists (unique violation),
        // return the existing booking with 200 instead of a 500
        $customer = $this->createCustomer();
        $token = Str::uuid()->toString();

        $existing = BhavBooking::create([
            'booking_no'       => 'BB-000001',
            'customer_id'      => $customer->id,
            'request_token'    => $token,
            'trade_type'       => 'SELL',
            'entry_type'       => 'ONLY_ENTRY',
            'metal'            => 'GOLD',
            'status'           => 'PENDING',
            'total_weight'     => 10.000,
            'delivered_weight' => 0.000,
            'total_amount'     => 70000.00,
            'received_amount'  => 0.00,
            'given_amount'     => 0.00,
            'booked_at'        => now(),
        ]);

        $response = $this->postJson('/bhav-bookings', [
            'request_token' => $token,
            'customer_id'   => $customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [['weight' => 10.000, 'rate' => 70000.00]],
        ]);

        $response->assertStatus(200);
        $this->assertEquals($existing->id, $response->json('id'));
    }
}
