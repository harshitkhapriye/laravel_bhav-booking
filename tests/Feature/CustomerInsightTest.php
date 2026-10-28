<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerInsightTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_insight_returns_correct_aggregates(): void
    {
        $customer = Customer::create([
            'customer_no' => 'CUST-INS-001',
            'name' => 'Insight Customer',
            'mobile' => '9826011199',
            'party_type' => 'CUSTOMER',
            'city' => 'Indore',
            'aadhaar_no' => '123412341234',
            'pan_no' => 'ABCDE1234F',
        ]);

        // 1. Delivered booking (completed) - contributes to total_bookings, not pending
        BhavBooking::create([
            'booking_no' => 'BB-000101',
            'customer_id' => $customer->id,
            'metal' => 'GOLD',
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'total_weight' => 5.000,
            'delivered_weight' => 5.000,
            'total_amount' => 50000,
            'received_amount' => 50000,
            'given_amount' => 0,
            'status' => 'DELIVERED',
            'request_token' => (string) Str::uuid(),
            'booked_at' => now()->subDays(5),
        ]);

        // 2. Pending SELL Gold booking - pending weight 8 gm, to_receive 40000
        BhavBooking::create([
            'booking_no' => 'BB-000102',
            'customer_id' => $customer->id,
            'metal' => 'GOLD',
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'total_weight' => 10.000,
            'delivered_weight' => 2.000,
            'total_amount' => 50000,
            'received_amount' => 10000,
            'given_amount' => 0,
            'status' => 'PENDING',
            'request_token' => (string) Str::uuid(),
            'booked_at' => now()->subDays(2),
        ]);

        // 3. Partial BUY Silver booking - pending weight 15 gm, to_give 20000
        BhavBooking::create([
            'booking_no' => 'BB-000103',
            'customer_id' => $customer->id,
            'metal' => 'SILVER',
            'trade_type' => 'BUY',
            'entry_type' => 'ONLY_ENTRY',
            'total_weight' => 20.000,
            'delivered_weight' => 5.000,
            'total_amount' => 30000,
            'given_amount' => 10000,
            'received_amount' => 0,
            'status' => 'PARTIAL',
            'request_token' => (string) Str::uuid(),
            'booked_at' => now()->subHours(5),
        ]);

        // 4. Soft-deleted booking - MUST BE IGNORED completely
        $deletedBooking = BhavBooking::create([
            'booking_no' => 'BB-000104',
            'customer_id' => $customer->id,
            'metal' => 'GOLD',
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'total_weight' => 50.000,
            'delivered_weight' => 0,
            'total_amount' => 500000,
            'received_amount' => 0,
            'given_amount' => 0,
            'status' => 'PENDING',
            'request_token' => (string) Str::uuid(),
            'booked_at' => now(),
        ]);
        $deletedBooking->delete();

        $response = $this->getJson("/customers/{$customer->id}/insight");

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertEquals(3, $data['total_bookings']);
        $this->assertEquals(2, $data['pending_bookings_count']);
        $this->assertEquals(8.000, $data['pending_weight']['gold']);
        $this->assertEquals(15.000, $data['pending_weight']['silver']);
        $this->assertEquals(40000.00, $data['to_receive']);
        $this->assertEquals(20000.00, $data['to_give']);
        $this->assertNotNull($data['last_booking_at']);

        // Verify sensitive fields are never present
        $this->assertArrayNotHasKey('aadhaar_no', $data);
        $this->assertArrayNotHasKey('pan_no', $data);
    }

    public function test_customer_insight_returns_404_when_customer_missing(): void
    {
        $response = $this->getJson('/customers/999999/insight');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Party not found']);
    }
}

