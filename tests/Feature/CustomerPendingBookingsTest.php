<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\BhavBookingItem;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerPendingBookingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_bookings_returns_only_pending_and_partial_and_not_soft_deleted(): void
    {
        $customer = Customer::create([
            'customer_no' => 'CUST-PB-001',
            'name' => 'Pending Customer',
            'mobile' => '9826011199',
            'party_type' => 'CUSTOMER',
            'city' => 'Indore',
            'aadhaar_no' => '123412341234',
            'pan_no' => 'ABCDE1234F',
        ]);

        // 1. DELIVERED booking (should be excluded)
        BhavBooking::create([
            'booking_no' => 'BB-000201',
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
            'booked_at' => now()->subDays(10),
        ]);

        // 2. CANCELLED booking (should be excluded)
        BhavBooking::create([
            'booking_no' => 'BB-000202',
            'customer_id' => $customer->id,
            'metal' => 'SILVER',
            'trade_type' => 'BUY',
            'entry_type' => 'ONLY_ENTRY',
            'total_weight' => 10.000,
            'delivered_weight' => 0,
            'total_amount' => 18000,
            'received_amount' => 0,
            'given_amount' => 0,
            'status' => 'CANCELLED',
            'request_token' => (string) Str::uuid(),
            'booked_at' => now()->subDays(9),
        ]);

        // 3. Soft-deleted PENDING booking (should be excluded)
        $deleted = BhavBooking::create([
            'booking_no' => 'BB-000203',
            'customer_id' => $customer->id,
            'metal' => 'GOLD',
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'total_weight' => 8.000,
            'delivered_weight' => 0,
            'total_amount' => 80000,
            'received_amount' => 0,
            'given_amount' => 0,
            'status' => 'PENDING',
            'request_token' => (string) Str::uuid(),
            'booked_at' => now()->subDays(8),
        ]);
        $deleted->delete();

        // 4. PENDING booking (should be included)
        $bPending = BhavBooking::create([
            'booking_no' => 'BB-000204',
            'customer_id' => $customer->id,
            'metal' => 'GOLD',
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'total_weight' => 10.000,
            'delivered_weight' => 0,
            'total_amount' => 100000,
            'received_amount' => 20000,
            'given_amount' => 0,
            'status' => 'PENDING',
            'request_token' => (string) Str::uuid(),
            'booked_at' => now()->subDays(2),
        ]);

        // 5. PARTIAL booking (should be included)
        $bPartial = BhavBooking::create([
            'booking_no' => 'BB-000205',
            'customer_id' => $customer->id,
            'metal' => 'SILVER',
            'trade_type' => 'BUY',
            'entry_type' => 'ONLY_ENTRY',
            'total_weight' => 20.000,
            'delivered_weight' => 5.000,
            'total_amount' => 36000,
            'received_amount' => 0,
            'given_amount' => 12000,
            'status' => 'PARTIAL',
            'request_token' => (string) Str::uuid(),
            'booked_at' => now()->subDays(1),
        ]);

        $response = $this->getJson("/customers/{$customer->id}/pending-bookings");

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertIsArray($data);
        $this->assertCount(2, $data);

        $bookingNos = array_column($data, 'booking_no');
        $this->assertContains('BB-000204', $bookingNos);
        $this->assertContains('BB-000205', $bookingNos);
        $this->assertNotContains('BB-000201', $bookingNos);
        $this->assertNotContains('BB-000202', $bookingNos);
        $this->assertNotContains('BB-000203', $bookingNos);
    }

    public function test_pending_bookings_ordered_newest_first_and_limited_to_20(): void
    {
        $customer = Customer::create([
            'customer_no' => 'CUST-PB-002',
            'name' => 'Many Bookings Customer',
            'mobile' => '9826011188',
            'party_type' => 'CUSTOMER',
        ]);

        // Create 25 pending bookings with increasing booked_at dates
        for ($i = 1; $i <= 25; $i++) {
            BhavBooking::create([
                'booking_no' => sprintf('BB-%06d', 300 + $i),
                'customer_id' => $customer->id,
                'metal' => 'GOLD',
                'trade_type' => 'SELL',
                'entry_type' => 'ONLY_ENTRY',
                'total_weight' => 1.000,
                'delivered_weight' => 0,
                'total_amount' => 10000,
                'received_amount' => 0,
                'given_amount' => 0,
                'status' => 'PENDING',
                'request_token' => (string) Str::uuid(),
                'booked_at' => now()->subDays(30 - $i), // #25 is newest (subDays(5))
            ]);
        }

        $response = $this->getJson("/customers/{$customer->id}/pending-bookings");

        $response->assertStatus(200);
        $data = $response->json();

        // Max 20 results
        $this->assertCount(20, $data);

        // First item should be the newest (#25: BB-000325)
        $this->assertEquals('BB-000325', $data[0]['booking_no']);

        // Verify strictly descending order
        for ($j = 0; $j < count($data) - 1; $j++) {
            $this->assertGreaterThanOrEqual(
                strtotime($data[$j + 1]['booked_at']),
                strtotime($data[$j]['booked_at'])
            );
        }
    }

    public function test_pending_bookings_balance_correct_for_sell_and_buy(): void
    {
        $customer = Customer::create([
            'customer_no' => 'CUST-PB-003',
            'name' => 'Balance Test Customer',
            'mobile' => '9826011177',
            'party_type' => 'CUSTOMER',
        ]);

        // SELL booking: total 50,000, received 15,000 -> paid = 15,000, balance = 35,000
        $sell = BhavBooking::create([
            'booking_no' => 'BB-000401',
            'customer_id' => $customer->id,
            'metal' => 'GOLD',
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'total_weight' => 3.000,
            'delivered_weight' => 0,
            'total_amount' => 50000.00,
            'received_amount' => 15000.00,
            'given_amount' => 0,
            'status' => 'PARTIAL',
            'request_token' => (string) Str::uuid(),
            'booked_at' => now()->subHours(2),
        ]);

        // BUY booking: total 30,000, given 10,000 -> paid = 10,000, balance = 20,000
        $buy = BhavBooking::create([
            'booking_no' => 'BB-000402',
            'customer_id' => $customer->id,
            'metal' => 'SILVER',
            'trade_type' => 'BUY',
            'entry_type' => 'ONLY_ENTRY',
            'total_weight' => 15.000,
            'delivered_weight' => 0,
            'total_amount' => 30000.00,
            'received_amount' => 0,
            'given_amount' => 10000.00,
            'status' => 'PENDING',
            'request_token' => (string) Str::uuid(),
            'booked_at' => now()->subHours(1),
        ]);

        $response = $this->getJson("/customers/{$customer->id}/pending-bookings");

        $response->assertStatus(200);
        $data = $response->json();

        // Index 0 is BUY (subHours(1)), Index 1 is SELL (subHours(2))
        $buyData = collect($data)->firstWhere('booking_no', 'BB-000402');
        $this->assertNotNull($buyData);
        $this->assertEquals(30000.00, $buyData['total_amount']);
        $this->assertEquals(10000.00, $buyData['paid']);
        $this->assertEquals(20000.00, $buyData['balance']);

        $sellData = collect($data)->firstWhere('booking_no', 'BB-000401');
        $this->assertNotNull($sellData);
        $this->assertEquals(50000.00, $sellData['total_amount']);
        $this->assertEquals(15000.00, $sellData['paid']);
        $this->assertEquals(35000.00, $sellData['balance']);
    }

    public function test_pending_bookings_includes_items_with_required_fields(): void
    {
        $customer = Customer::create([
            'customer_no' => 'CUST-PB-004',
            'name' => 'Items Customer',
            'mobile' => '9826011166',
            'party_type' => 'CUSTOMER',
        ]);

        $booking = BhavBooking::create([
            'booking_no' => 'BB-000501',
            'customer_id' => $customer->id,
            'metal' => 'GOLD',
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'total_weight' => 5.000,
            'delivered_weight' => 0,
            'total_amount' => 82500.00,
            'received_amount' => 0,
            'given_amount' => 0,
            'status' => 'PENDING',
            'request_token' => (string) Str::uuid(),
            'booked_at' => now(),
        ]);

        BhavBookingItem::create([
            'bhav_booking_id' => $booking->id,
            'sort_order' => 1,
            'metal' => 'GOLD',
            'weight' => 2.000,
            'rate' => 165000.00,
            'amount' => 33000.00,
        ]);

        BhavBookingItem::create([
            'bhav_booking_id' => $booking->id,
            'sort_order' => 2,
            'metal' => 'GOLD',
            'weight' => 3.000,
            'rate' => 165000.00,
            'amount' => 49500.00,
        ]);

        $response = $this->getJson("/customers/{$customer->id}/pending-bookings");

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(1, $data);
        $first = $data[0];

        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('booking_no', $first);
        $this->assertArrayHasKey('booked_at', $first);
        $this->assertArrayHasKey('trade_type', $first);
        $this->assertArrayHasKey('metal', $first);
        $this->assertArrayHasKey('entry_type', $first);
        $this->assertArrayHasKey('total_weight', $first);
        $this->assertArrayHasKey('total_amount', $first);
        $this->assertArrayHasKey('paid', $first);
        $this->assertArrayHasKey('balance', $first);
        $this->assertArrayHasKey('items', $first);

        $this->assertCount(2, $first['items']);
        $this->assertEquals(2.000, $first['items'][0]['weight']);
        $this->assertEquals(165000.00, $first['items'][0]['rate']);
        $this->assertEquals(33000.00, $first['items'][0]['amount']);
        $this->assertEquals('GOLD', $first['items'][0]['metal']);

        $this->assertEquals(3.000, $first['items'][1]['weight']);
        $this->assertEquals(165000.00, $first['items'][1]['rate']);
        $this->assertEquals(49500.00, $first['items'][1]['amount']);
        $this->assertEquals('GOLD', $first['items'][1]['metal']);
    }

    public function test_pending_bookings_returns_404_when_customer_missing(): void
    {
        $response = $this->getJson('/customers/999999/pending-bookings');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Party not found']);
    }

    public function test_pending_bookings_never_returns_aadhaar_or_pan(): void
    {
        $customer = Customer::create([
            'customer_no' => 'CUST-PB-005',
            'name' => 'Sensitive Test Customer',
            'mobile' => '9826011155',
            'party_type' => 'CUSTOMER',
            'aadhaar_no' => '999988887777',
            'pan_no' => 'SENS1234P',
        ]);

        BhavBooking::create([
            'booking_no' => 'BB-000601',
            'customer_id' => $customer->id,
            'metal' => 'GOLD',
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'total_weight' => 1.000,
            'delivered_weight' => 0,
            'total_amount' => 16500.00,
            'received_amount' => 0,
            'given_amount' => 0,
            'status' => 'PENDING',
            'request_token' => (string) Str::uuid(),
            'booked_at' => now(),
        ]);

        $response = $this->getJson("/customers/{$customer->id}/pending-bookings");

        $response->assertStatus(200);
        $rawJson = $response->getContent();

        $this->assertStringNotContainsString('999988887777', $rawJson);
        $this->assertStringNotContainsString('SENS1234P', $rawJson);
        $this->assertStringNotContainsString('aadhaar', strtolower($rawJson));
        $this->assertStringNotContainsString('pan_no', strtolower($rawJson));
    }
}

