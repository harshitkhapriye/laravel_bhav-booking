<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BhavBookingPrintQATest extends TestCase
{
    use RefreshDatabase;

    public function test_print_page_returns_200_and_shows_db_values(): void
    {
        $customer = Customer::create([
            'customer_no' => 'CUST-0001',
            'name' => 'Print Test Customer',
            'mobile' => '9826011122',
            'party_type' => 'CUSTOMER',
            'city' => 'Indore',
        ]);

        $booking = BhavBooking::create([
            'booking_no' => 'BB-000001',
            'customer_id' => $customer->id,
            'request_token' => Str::uuid()->toString(),
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'status' => 'PENDING',
            'total_weight' => 10.000,
            'delivered_weight' => 0.000,
            'total_amount' => 70000.00,
            'received_amount' => 20000.00,
            'given_amount' => 0.00,
            'booked_at' => now(),
        ]);

        $booking->items()->create([
            'sort_order' => 1,
            'weight' => 10.000,
            'rate' => 70000.00,
            'amount' => 70000.00,
        ]);

        $response = $this->get("/bhav-bookings/{$booking->id}/print");
        $response->assertStatus(200);
        $response->assertSee('BB-000001');
        $response->assertSee('Print Test Customer');
        $response->assertSee('10.000');
        $response->assertSee('70,000.00');
    }

    public function test_print_page_returns_404_for_missing_booking(): void
    {
        $response = $this->get('/bhav-bookings/999999/print');
        $response->assertStatus(404);
    }
}
