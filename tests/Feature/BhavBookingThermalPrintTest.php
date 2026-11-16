<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BhavBookingThermalPrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_print_with_format_thermal_returns_200_and_contains_booking_no_and_total(): void
    {
        $customer = Customer::create([
            'customer_no' => 'CUST-0099',
            'name' => 'Thermal Test Customer',
            'mobile' => '9876543210',
            'party_type' => 'CUSTOMER',
            'city' => 'Indore',
        ]);

        $booking = BhavBooking::create([
            'booking_no' => 'BB-000099',
            'customer_id' => $customer->id,
            'request_token' => Str::uuid()->toString(),
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'status' => 'PENDING',
            'total_weight' => 5.000,
            'delivered_weight' => 0.000,
            'total_amount' => 45000.00,
            'received_amount' => 15000.00,
            'given_amount' => 0.00,
            'booked_at' => now(),
        ]);

        $booking->items()->create([
            'sort_order' => 1,
            'weight' => 5.000,
            'rate' => 90000.00,
            'amount' => 45000.00,
        ]);

        $response = $this->get("/bhav-bookings/{$booking->id}/print?format=thermal");
        $response->assertStatus(200);
        $response->assertSee('BB-000099');
        $response->assertSee('45,000.00');
        $response->assertSee('Thermal Print');
        $response->assertSee('size: 80mm auto', false);
    }

    public function test_print_with_invalid_format_falls_back_to_a4(): void
    {
        $customer = Customer::create([
            'customer_no' => 'CUST-0100',
            'name' => 'Fallback Customer',
            'mobile' => '9876543211',
            'party_type' => 'CUSTOMER',
        ]);

        $booking = BhavBooking::create([
            'booking_no' => 'BB-000100',
            'customer_id' => $customer->id,
            'request_token' => Str::uuid()->toString(),
            'trade_type' => 'BUY',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'SILVER',
            'status' => 'PENDING',
            'total_weight' => 50.000,
            'delivered_weight' => 0.000,
            'total_amount' => 9000.00,
            'received_amount' => 0.00,
            'given_amount' => 5000.00,
            'booked_at' => now(),
        ]);

        $response = $this->get("/bhav-bookings/{$booking->id}/print?format=unknown_format");
        $response->assertStatus(200);
        $response->assertSee('BB-000100');
        $response->assertSee('Bhav Booking Confirmation Voucher');
        $response->assertDontSee('size: 80mm auto', false);
    }

    public function test_group_print_with_format_thermal_returns_200_and_contains_booking_no_and_total(): void
    {
        $customer = Customer::create([
            'customer_no' => 'CUST-0101',
            'name' => 'Group Thermal Customer',
            'mobile' => '9876543212',
            'party_type' => 'CUSTOMER',
        ]);

        $groupId = (string) Str::uuid();

        $booking1 = BhavBooking::create([
            'booking_no' => 'BB-000101',
            'booking_group_id' => $groupId,
            'customer_id' => $customer->id,
            'request_token' => Str::uuid()->toString(),
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'status' => 'PENDING',
            'total_weight' => 2.000,
            'delivered_weight' => 0.000,
            'total_amount' => 18000.00,
            'received_amount' => 0.00,
            'given_amount' => 0.00,
            'booked_at' => now(),
        ]);

        $booking2 = BhavBooking::create([
            'booking_no' => 'BB-000102',
            'booking_group_id' => $groupId,
            'customer_id' => $customer->id,
            'request_token' => Str::uuid()->toString(),
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'SILVER',
            'status' => 'PENDING',
            'total_weight' => 10.000,
            'delivered_weight' => 0.000,
            'total_amount' => 1800.00,
            'received_amount' => 0.00,
            'given_amount' => 0.00,
            'booked_at' => now(),
        ]);

        $response = $this->get("/bhav-bookings/group/{$groupId}/print?format=thermal");
        $response->assertStatus(200);
        $response->assertSee('BB-000101');
        $response->assertSee('BB-000102');
        $response->assertSee('19,800.00'); // Combined grand total
        $response->assertSee('size: 80mm auto', false);
    }
}
