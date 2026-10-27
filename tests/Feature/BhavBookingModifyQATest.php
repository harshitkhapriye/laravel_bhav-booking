<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\BhavBookingPayment;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BhavBookingModifyQATest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(string $name = 'Primary Customer'): Customer
    {
        return Customer::create([
            'customer_no' => 'CUST-'.rand(1000, 9999),
            'name' => $name,
            'mobile' => '98'.rand(10000000, 99999999),
            'party_type' => 'CUSTOMER',
            'city' => 'Indore',
        ]);
    }

    private function createPendingBooking(Customer $customer): BhavBooking
    {
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
            'received_amount' => 0.00,
            'given_amount' => 0.00,
            'booked_at' => now(),
        ]);

        $booking->items()->create([
            'sort_order' => 1,
            'weight' => 10.000,
            'rate' => 70000.00,
            'amount' => 70000.00,
        ]);

        return $booking;
    }

    public function test_update_booking_customer_cannot_change_even_if_sent(): void
    {
        $originalCustomer = $this->createCustomer('Original');
        $differentCustomer = $this->createCustomer('Different');
        $booking = $this->createPendingBooking($originalCustomer);

        $response = $this->putJson("/bhav-bookings/{$booking->id}", [
            'customer_id' => $differentCustomer->id, // Attempt to tamper customer
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['weight' => 10.000, 'rate' => 70000.00],
            ],
        ]);

        $response->assertStatus(200);
        $booking->refresh();
        // Customer MUST remain original
        $this->assertEquals($originalCustomer->id, $booking->customer_id);
    }

    public function test_update_booking_items_replaced_correctly_and_totals_recalculated(): void
    {
        $customer = $this->createCustomer();
        $booking = $this->createPendingBooking($customer);

        // Replace 1 item (10gm @ 70,000 = 70,000) with 2 items (5gm @ 72,000 + 5gm @ 72,000 = 72,000)
        $response = $this->putJson("/bhav-bookings/{$booking->id}", [
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['weight' => 5.000, 'rate' => 72000.00],
                ['weight' => 5.000, 'rate' => 72000.00],
            ],
        ]);

        $response->assertStatus(200);
        $booking->refresh();

        $this->assertEquals('10.000', $booking->total_weight);
        $this->assertEquals('72000.00', $booking->total_amount);
        $this->assertCount(2, $booking->items);
        $this->assertEquals('36000.00', $booking->items[0]->amount);
        $this->assertEquals('36000.00', $booking->items[1]->amount);
    }

    public function test_update_blocked_when_not_modifiable_returns_422_with_sop_message(): void
    {
        // Bug e: Use the SOP message for blocked edit/delete: "This booking has delivery/payment and cannot be edited"
        $customer = $this->createCustomer();
        $booking = $this->createPendingBooking($customer);

        // Mark as PARTIAL delivery (delivered_weight > 0)
        $booking->update([
            'status' => 'PARTIAL',
            'delivered_weight' => 2.000,
        ]);

        $response = $this->putJson("/bhav-bookings/{$booking->id}", [
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['weight' => 10.000, 'rate' => 70000.00],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertEquals(
            'This booking has delivery/payment and cannot be edited',
            $response->json('message') ?? $response->json('booking.0')
        );
    }

    public function test_update_returns_404_for_missing_booking_id(): void
    {
        $response = $this->putJson('/bhav-bookings/999999', [
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['weight' => 10.000, 'rate' => 70000.00],
            ],
        ]);

        $response->assertStatus(404);
    }

    public function test_soft_delete_works(): void
    {
        $customer = $this->createCustomer();
        $booking = $this->createPendingBooking($customer);

        $response = $this->deleteJson("/bhav-bookings/{$booking->id}");
        $response->assertStatus(200);

        $this->assertSoftDeleted('bhav_bookings', ['id' => $booking->id]);
    }

    public function test_delete_blocked_when_not_modifiable_returns_422_with_sop_message(): void
    {
        // Bug e: Use the SOP message for blocked edit/delete: "This booking has delivery/payment and cannot be edited"
        $customer = $this->createCustomer();
        $booking = $this->createPendingBooking($customer);

        // Add external BUY_SELL payment
        BhavBookingPayment::create([
            'bhav_booking_id' => $booking->id,
            'mode' => 'CASH',
            'amount' => 10000.00,
            'source' => 'BUY_SELL',
            'sort_order' => 1,
            'paid_at' => now(),
        ]);

        $response = $this->deleteJson("/bhav-bookings/{$booking->id}");
        $response->assertStatus(422);
        $this->assertEquals(
            'This booking has delivery/payment and cannot be edited',
            $response->json('message') ?? $response->json('booking.0')
        );

        $this->assertNotSoftDeleted('bhav_bookings', ['id' => $booking->id]);
    }

    public function test_deleted_bookings_disappear_from_list_search_recent(): void
    {
        $customer = $this->createCustomer('Soft Delete Target');
        $booking = $this->createPendingBooking($customer);

        // Verify present in list
        $resBefore = $this->getJson('/bhav-bookings/list');
        $resBefore->assertStatus(200);
        $this->assertContains($booking->id, collect($resBefore->json('rows'))->pluck('id')->toArray());

        // Delete booking
        $delRes = $this->deleteJson("/bhav-bookings/{$booking->id}");
        $delRes->assertStatus(200);

        // Verify disappears from list
        $resAfter = $this->getJson('/bhav-bookings/list');
        $resAfter->assertStatus(200);
        $this->assertNotContains($booking->id, collect($resAfter->json('rows'))->pluck('id')->toArray());

        // Verify disappears from customer recent ordering
        $recentRes = $this->getJson('/customers/recent');
        $recentRes->assertStatus(200);
        // Customer should not be ordered by that deleted booking
    }
}
