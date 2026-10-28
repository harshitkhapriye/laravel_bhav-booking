<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\BhavBookingLog;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BhavBookingRestoreTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(): Customer
    {
        return Customer::create([
            'customer_no' => 'CUST-RESTORE-01',
            'name' => 'Restore Customer',
            'mobile' => '9826011188',
            'party_type' => 'CUSTOMER',
            'city' => 'Indore',
        ]);
    }

    private function createBooking(Customer $customer, array $attributes = []): BhavBooking
    {
        return BhavBooking::create(array_merge([
            'booking_no' => 'BB-000201',
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
    }

    public function test_restore_within_5_minutes_succeeds_and_logs_restored_action(): void
    {
        $customer = $this->createCustomer();
        $booking = $this->createBooking($customer);
        $booking->delete();

        // Update deleted_at to 2 minutes ago
        $booking->deleted_at = now()->subMinutes(2);
        $booking->save();

        $this->assertSoftDeleted('bhav_bookings', ['id' => $booking->id]);

        $response = $this->postJson("/bhav-bookings/{$booking->id}/restore");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Booking restored successfully.',
        ]);

        $this->assertNotSoftDeleted('bhav_bookings', ['id' => $booking->id]);

        // Check log entry
        $this->assertDatabaseHas('bhav_booking_logs', [
            'bhav_booking_id' => $booking->id,
            'action' => 'RESTORED',
        ]);

        $log = BhavBookingLog::where('bhav_booking_id', $booking->id)->where('action', 'RESTORED')->first();
        $this->assertNotNull($log);
        $this->assertEquals('Restored booking', $log->summary);
    }

    public function test_restore_after_5_minutes_returns_422(): void
    {
        $customer = $this->createCustomer();
        $booking = $this->createBooking($customer);
        $booking->delete();

        // Update deleted_at to 6 minutes ago
        $booking->deleted_at = now()->subMinutes(6);
        $booking->save();

        $response = $this->postJson("/bhav-bookings/{$booking->id}/restore");

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Undo time expired',
        ]);

        $this->assertSoftDeleted('bhav_bookings', ['id' => $booking->id]);
    }

    public function test_restore_non_deleted_booking_returns_422(): void
    {
        $customer = $this->createCustomer();
        $booking = $this->createBooking($customer);

        $response = $this->postJson("/bhav-bookings/{$booking->id}/restore");

        $response->assertStatus(422);
        $this->assertNotSoftDeleted('bhav_bookings', ['id' => $booking->id]);
    }

    public function test_restore_missing_booking_returns_404(): void
    {
        $response = $this->postJson('/bhav-bookings/999999/restore');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Booking not found']);
    }

    public function test_restore_only_restores_the_single_booking_even_in_group(): void
    {
        $customer = $this->createCustomer();
        $groupId = 'group-uuid-12345';

        $booking1 = $this->createBooking($customer, [
            'booking_no' => 'BB-000202',
            'booking_group_id' => $groupId,
        ]);
        $booking1->delete();
        $booking1->deleted_at = now()->subMinutes(1);
        $booking1->save();

        $booking2 = $this->createBooking($customer, [
            'booking_no' => 'BB-000203',
            'booking_group_id' => $groupId,
        ]);
        $booking2->delete();
        $booking2->deleted_at = now()->subMinutes(1);
        $booking2->save();

        $response = $this->postJson("/bhav-bookings/{$booking1->id}/restore");

        $response->assertStatus(200);

        // Booking 1 is restored
        $this->assertNotSoftDeleted('bhav_bookings', ['id' => $booking1->id]);

        // Booking 2 is still soft deleted
        $this->assertSoftDeleted('bhav_bookings', ['id' => $booking2->id]);
    }
}

