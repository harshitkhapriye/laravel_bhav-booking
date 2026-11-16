<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\Customer;
use App\Models\DailyRate;
use App\Services\BhavBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BhavBookingRateDiffTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(): Customer
    {
        return Customer::create([
            'customer_no' => 'CUST-RD-01',
            'name' => 'Rate Diff Test Party',
            'mobile' => '9826099999',
            'party_type' => 'CUSTOMER',
            'city' => 'Indore',
        ]);
    }

    private function createBooking(Customer $customer, array $overrides = []): BhavBooking
    {
        return BhavBooking::create(array_merge([
            'booking_no' => 'BB-TEST-' . Str::upper(Str::random(6)),
            'booking_group_id' => (string) Str::uuid(),
            'request_token' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'total_weight' => 2.000,
            'total_amount' => 32000.00,
            'delivered_weight' => 0.000,
            'given_amount' => 0.00,
            'received_amount' => 0.00,
            'status' => 'PENDING',
            'booked_at' => now(),
        ], $overrides));
    }

    /**
     * Test SELL case with known numbers:
     * 2 gm booked at 1,60,000 /10gm, today 1,65,000 /10gm -> -1,000.00
     */
    public function test_sell_rate_difference_calculated_correctly_with_known_numbers(): void
    {
        $today = now()->toDateString();
        DailyRate::create([
            'metal' => 'GOLD',
            'rate' => 165000,
            'rate_date' => $today,
        ]);

        $customer = $this->createCustomer();
        // total_amount = 2 * 160000 / 10 = 32000
        $booking = $this->createBooking($customer, [
            'booking_no' => 'BB-SELL-01',
            'trade_type' => 'SELL',
            'metal' => 'GOLD',
            'total_weight' => 2.000,
            'total_amount' => 32000.00,
            'delivered_weight' => 0.000,
            'status' => 'PENDING',
        ]);

        $res = BhavBookingService::rateDifference($booking);

        $this->assertNotNull($res);
        $this->assertEquals(-1000.00, $res['rate_diff']);
        $this->assertEquals(160000.00, $res['booked_avg_rate']);
        $this->assertEquals(165000.00, $res['today_rate_used']);
    }

    /**
     * Test BUY case with known numbers:
     * 2 gm booked at 1,60,000 /10gm, today 1,65,000 /10gm -> +1,000.00
     */
    public function test_buy_rate_difference_calculated_correctly_with_known_numbers(): void
    {
        $today = now()->toDateString();
        DailyRate::create([
            'metal' => 'GOLD',
            'rate' => 165000,
            'rate_date' => $today,
        ]);

        $customer = $this->createCustomer();
        // total_amount = 2 * 160000 / 10 = 32000
        $booking = $this->createBooking($customer, [
            'booking_no' => 'BB-BUY-01',
            'trade_type' => 'BUY',
            'metal' => 'GOLD',
            'total_weight' => 2.000,
            'total_amount' => 32000.00,
            'delivered_weight' => 0.000,
            'status' => 'PENDING',
        ]);

        $res = BhavBookingService::rateDifference($booking);

        $this->assertNotNull($res);
        $this->assertEquals(1000.00, $res['rate_diff']);
        $this->assertEquals(160000.00, $res['booked_avg_rate']);
        $this->assertEquals(165000.00, $res['today_rate_used']);
    }

    /**
     * Test partial delivery uses only pending weight:
     * Total weight 5.000 gm, delivered 3.000 gm -> pending 2.000 gm.
     * Rate booked 1,60,000, today 1,65,000 -> -1,000.00
     */
    public function test_partial_delivery_uses_only_pending_weight(): void
    {
        $today = now()->toDateString();
        DailyRate::create([
            'metal' => 'GOLD',
            'rate' => 165000,
            'rate_date' => $today,
        ]);

        $customer = $this->createCustomer();
        // 5 gm at 160000 / 10 = 80000
        $booking = $this->createBooking($customer, [
            'booking_no' => 'BB-PARTIAL-01',
            'trade_type' => 'SELL',
            'metal' => 'GOLD',
            'total_weight' => 5.000,
            'total_amount' => 80000.00,
            'delivered_weight' => 3.000,
            'status' => 'PARTIAL',
        ]);

        $res = BhavBookingService::rateDifference($booking);

        $this->assertNotNull($res);
        $this->assertEquals(-1000.00, $res['rate_diff']);
        $this->assertEquals(160000.00, $res['booked_avg_rate']);
        $this->assertEquals(165000.00, $res['today_rate_used']);
    }

    /**
     * Test rateDifference returns null when today's rate is not set (is_set = false).
     */
    public function test_rate_difference_returns_null_when_today_rate_not_set(): void
    {
        // No DailyRate created for today
        $customer = $this->createCustomer();
        $booking = $this->createBooking($customer, [
            'booking_no' => 'BB-NO-RATE-01',
            'trade_type' => 'SELL',
            'metal' => 'GOLD',
            'total_weight' => 2.000,
            'total_amount' => 32000.00,
            'delivered_weight' => 0.000,
            'status' => 'PENDING',
        ]);

        $res = BhavBookingService::rateDifference($booking);
        $this->assertNull($res);
    }

    /**
     * Test rateDifference returns null for DELIVERED and CANCELLED bookings.
     */
    public function test_rate_difference_returns_null_for_delivered_and_cancelled_bookings(): void
    {
        $today = now()->toDateString();
        DailyRate::create([
            'metal' => 'GOLD',
            'rate' => 165000,
            'rate_date' => $today,
        ]);

        $customer = $this->createCustomer();
        $deliveredBooking = $this->createBooking($customer, [
            'booking_no' => 'BB-DELIVERED-01',
            'trade_type' => 'SELL',
            'metal' => 'GOLD',
            'total_weight' => 2.000,
            'total_amount' => 32000.00,
            'delivered_weight' => 2.000,
            'status' => 'DELIVERED',
        ]);

        $cancelledBooking = $this->createBooking($customer, [
            'booking_no' => 'BB-CANCELLED-01',
            'trade_type' => 'SELL',
            'metal' => 'GOLD',
            'total_weight' => 2.000,
            'total_amount' => 32000.00,
            'delivered_weight' => 0.000,
            'status' => 'CANCELLED',
            'cancel_reason' => 'Customer cancelled',
            'cancelled_at' => now(),
        ]);

        $this->assertNull(BhavBookingService::rateDifference($deliveredBooking));
        $this->assertNull(BhavBookingService::rateDifference($cancelledBooking));
    }

    /**
     * Test list endpoint returns rate_diff, booked_avg_rate, today_rate_used on rows
     * and rate_diff_total as the sum over all filtered rows.
     */
    public function test_list_endpoint_includes_rate_diff_and_filtered_rate_diff_total(): void
    {
        $today = now()->toDateString();
        DailyRate::create([
            'metal' => 'GOLD',
            'rate' => 165000,
            'rate_date' => $today,
        ]);

        $customer = $this->createCustomer();

        // 1. SELL 2 gm @ 160,000 -> -1,000.00
        $this->createBooking($customer, [
            'booking_no' => 'BB-SUM-01',
            'trade_type' => 'SELL',
            'metal' => 'GOLD',
            'total_weight' => 2.000,
            'total_amount' => 32000.00,
            'delivered_weight' => 0.000,
            'status' => 'PENDING',
        ]);

        // 2. BUY 2 gm @ 160,000 -> +1,000.00
        $this->createBooking($customer, [
            'booking_no' => 'BB-SUM-02',
            'trade_type' => 'BUY',
            'metal' => 'GOLD',
            'total_weight' => 2.000,
            'total_amount' => 32000.00,
            'delivered_weight' => 0.000,
            'status' => 'PENDING',
        ]);

        // 3. SELL 4 gm @ 160,000 -> -2,000.00
        $this->createBooking($customer, [
            'booking_no' => 'BB-SUM-03',
            'trade_type' => 'SELL',
            'metal' => 'GOLD',
            'total_weight' => 4.000,
            'total_amount' => 64000.00,
            'delivered_weight' => 0.000,
            'status' => 'PENDING',
        ]);

        // Total expected = -1000 + 1000 + (-2000) = -2000.00
        $response = $this->getJson('/bhav-bookings/list?view=current');
        $response->assertStatus(200);

        $json = $response->json();
        $this->assertEquals(-2000.00, $json['totals']['rate_diff_total']);

        $rows = $json['rows'];
        $this->assertCount(3, $rows);
        foreach ($rows as $row) {
            $this->assertArrayHasKey('rate_diff', $row);
            $this->assertArrayHasKey('booked_avg_rate', $row);
            $this->assertArrayHasKey('today_rate_used', $row);
            $this->assertNotNull($row['rate_diff']);
            $this->assertEquals(160000.00, $row['booked_avg_rate']);
            $this->assertEquals(165000.00, $row['today_rate_used']);
        }
    }

    /**
     * Test show endpoint includes rate_diff, booked_avg_rate, and today_rate_used.
     */
    public function test_show_endpoint_includes_rate_diff_fields(): void
    {
        $today = now()->toDateString();
        DailyRate::create([
            'metal' => 'GOLD',
            'rate' => 165000,
            'rate_date' => $today,
        ]);

        $customer = $this->createCustomer();
        $booking = $this->createBooking($customer, [
            'booking_no' => 'BB-SHOW-01',
            'trade_type' => 'SELL',
            'metal' => 'GOLD',
            'total_weight' => 2.000,
            'total_amount' => 32000.00,
            'delivered_weight' => 0.000,
            'status' => 'PENDING',
        ]);

        $response = $this->getJson("/bhav-bookings/{$booking->id}");
        $response->assertStatus(200);

        $response->assertJson([
            'rate_diff' => -1000.00,
            'booked_avg_rate' => 160000.00,
            'today_rate_used' => 165000.00,
        ]);
    }
}
