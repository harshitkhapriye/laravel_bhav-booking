<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\BhavBookingItem;
use App\Models\BhavBookingLog;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BhavBookingCancelTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(): Customer
    {
        return Customer::create([
            'customer_no' => 'CUST-CANCEL-01',
            'name' => 'Cancel Test Customer',
            'mobile' => '9826011199',
            'party_type' => 'CUSTOMER',
            'city' => 'Indore',
        ]);
    }

    private function createBooking(Customer $customer, array $attributes = []): BhavBooking
    {
        $booking = BhavBooking::create(array_merge([
            'booking_no' => 'BB-'.str_pad((string) mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'customer_id' => $customer->id,
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'total_weight' => 10.000,
            'total_amount' => 70000.00,
            'delivered_weight' => 0.000,
            'given_amount' => 0.00,
            'received_amount' => 0.00,
            'status' => 'PENDING',
            'request_token' => (string) Str::uuid(),
            'booked_at' => now(),
        ], $attributes));

        BhavBookingItem::create([
            'bhav_booking_id' => $booking->id,
            'metal' => $booking->metal,
            'sort_order' => 1,
            'weight' => $booking->total_weight,
            'rate' => 70000.00,
            'amount' => $booking->total_amount,
        ]);

        return $booking;
    }

    public function test_cancel_works_for_pending_booking_with_reason(): void
    {
        $customer = $this->createCustomer();
        $booking = $this->createBooking($customer);

        $response = $this->postJson("/bhav-bookings/{$booking->id}/cancel", [
            'reason' => 'Customer changed mind',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Booking cancelled successfully.',
            ]);

        $this->assertDatabaseHas('bhav_bookings', [
            'id' => $booking->id,
            'status' => 'CANCELLED',
            'cancel_reason' => 'Customer changed mind',
        ]);

        $booking->refresh();
        $this->assertNotNull($booking->cancelled_at);

        // Activity log is written
        $this->assertDatabaseHas('bhav_booking_logs', [
            'bhav_booking_id' => $booking->id,
            'action' => 'CANCELLED',
        ]);

        $log = BhavBookingLog::where('bhav_booking_id', $booking->id)->where('action', 'CANCELLED')->first();
        $this->assertNotNull($log);
        $this->assertEquals('Customer changed mind', $log->new_values['reason']);
        $this->assertStringContainsString('Customer changed mind', $log->summary);

        // Appears in view=old list
        $oldRes = $this->getJson('/bhav-bookings/list?view=old');
        $oldRes->assertStatus(200);
        $oldIds = collect($oldRes->json('rows'))->pluck('id')->all();
        $this->assertContains($booking->id, $oldIds);

        // Disappears from view=current list
        $currRes = $this->getJson('/bhav-bookings/list?view=current');
        $currRes->assertStatus(200);
        $currIds = collect($currRes->json('rows'))->pluck('id')->all();
        $this->assertNotContains($booking->id, $currIds);
    }

    public function test_cancel_works_for_partial_booking_without_delivery(): void
    {
        $customer = $this->createCustomer();
        $booking = $this->createBooking($customer, [
            'status' => 'PARTIAL',
            'delivered_weight' => 0.000,
            'received_amount' => 10000.00,
        ]);

        $response = $this->postJson("/bhav-bookings/{$booking->id}/cancel", [
            'reason' => 'Rate dispute',
        ]);

        $response->assertStatus(200);
        $booking->refresh();
        $this->assertEquals('CANCELLED', $booking->status);
        $this->assertEquals('Rate dispute', $booking->cancel_reason);
        // Payments / received amount remain intact (no auto refund)
        $this->assertEquals(10000.00, (float) $booking->received_amount);
    }

    public function test_cancel_rejected_without_reason_or_too_short(): void
    {
        $customer = $this->createCustomer();
        $booking = $this->createBooking($customer);

        // Empty reason
        $resEmpty = $this->postJson("/bhav-bookings/{$booking->id}/cancel", [
            'reason' => '',
        ]);
        $resEmpty->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);

        // Less than 3 chars
        $resShort = $this->postJson("/bhav-bookings/{$booking->id}/cancel", [
            'reason' => 'ab',
        ]);
        $resShort->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_cancel_rejected_with_delivered_weight(): void
    {
        $customer = $this->createCustomer();
        $booking = $this->createBooking($customer, [
            'delivered_weight' => 2.500,
        ]);

        $response = $this->postJson("/bhav-bookings/{$booking->id}/cancel", [
            'reason' => 'Customer changed mind',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'This booking has delivery and cannot be cancelled',
            ]);
    }

    public function test_cancel_rejected_for_already_cancelled_booking(): void
    {
        $customer = $this->createCustomer();
        $booking = $this->createBooking($customer, [
            'status' => 'CANCELLED',
            'cancel_reason' => 'Prior cancellation',
            'cancelled_at' => now(),
        ]);

        $response = $this->postJson("/bhav-bookings/{$booking->id}/cancel", [
            'reason' => 'Trying to cancel again',
        ]);

        $response->assertStatus(422);
    }

    public function test_cancelled_booking_cannot_be_updated_or_deleted(): void
    {
        $customer = $this->createCustomer();
        $booking = $this->createBooking($customer);

        // Cancel it
        $this->postJson("/bhav-bookings/{$booking->id}/cancel", [
            'reason' => 'Wrong entry',
        ])->assertStatus(200);

        // Attempt Update -> 422
        $updateRes = $this->putJson("/bhav-bookings/{$booking->id}", [
            'customer_id' => $customer->id,
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['weight' => 12.000, 'rate' => 70000.00],
            ],
            'payments' => [],
        ]);
        $updateRes->assertStatus(422);

        // Attempt Delete -> 422
        $delRes = $this->deleteJson("/bhav-bookings/{$booking->id}");
        $delRes->assertStatus(422);
    }

    public function test_insight_and_pending_bookings_ignore_cancelled_bookings(): void
    {
        $customer = $this->createCustomer();

        // 1 active pending booking
        $activeBooking = $this->createBooking($customer, [
            'metal' => 'GOLD',
            'total_weight' => 5.000,
            'total_amount' => 35000.00,
            'delivered_weight' => 0.000,
            'received_amount' => 0.00,
            'status' => 'PENDING',
        ]);

        // 1 cancelled booking
        $cancelledBooking = $this->createBooking($customer, [
            'metal' => 'GOLD',
            'total_weight' => 10.000,
            'total_amount' => 70000.00,
            'delivered_weight' => 0.000,
            'received_amount' => 0.00,
            'status' => 'CANCELLED',
            'cancel_reason' => 'Cancelled by party',
            'cancelled_at' => now(),
        ]);

        // 1. GET /customers/{customer}/insight
        $insightRes = $this->getJson("/customers/{$customer->id}/insight");
        $insightRes->assertStatus(200);
        $insightData = $insightRes->json();

        // Cancelled bookings must NOT be counted in total_bookings or pending stats
        $this->assertEquals(1, $insightData['total_bookings']);
        $this->assertEquals(1, $insightData['pending_bookings_count']);
        $this->assertEquals(5.000, $insightData['pending_gold_weight']);
        $this->assertEquals(35000.00, $insightData['to_receive']);

        // 2. GET /customers/{customer}/pending-bookings
        $pendingRes = $this->getJson("/customers/{$customer->id}/pending-bookings");
        $pendingRes->assertStatus(200);
        $pendingBookings = $pendingRes->json();

        $this->assertCount(1, $pendingBookings);
        $this->assertEquals($activeBooking->id, $pendingBookings[0]['id']);
    }
}
