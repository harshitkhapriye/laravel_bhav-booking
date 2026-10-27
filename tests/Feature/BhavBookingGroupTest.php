<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BhavBookingGroupTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(): Customer
    {
        return Customer::create([
            'customer_no' => 'CUST-'.rand(1000, 9999),
            'name' => 'Group Test Customer',
            'mobile' => '98'.rand(10000000, 99999999),
            'party_type' => 'CUSTOMER',
            'city' => 'Indore',
        ]);
    }

    /**
     * a) SELL: Gold 2gm × 165000 + Silver 10gm × 1800 in one request -> 2 bookings:
     * Gold ₹33,000.00, Silver ₹1,800.00, same booking_group_id, different booking_no,
     * each booking's items only of its own metal.
     */
    public function test_sell_multi_metal_request_creates_two_linked_bookings(): void
    {
        $customer = $this->createCustomer();
        $requestToken = Str::uuid()->toString();

        $payload = [
            'customer_id' => $customer->id,
            'request_token' => $requestToken,
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'items' => [
                ['metal' => 'GOLD', 'weight' => 2.000, 'rate' => 165000.00], // 2 * 165000 / 10 = 33000
                ['metal' => 'SILVER', 'weight' => 10.000, 'rate' => 1800.00], // 10 * 1800 / 10 = 1800
            ],
        ];

        $response = $this->postJson('/bhav-bookings', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'bookings',
            'group_id',
        ]);

        $this->assertEquals($requestToken, $response->json('group_id'));
        $bookings = $response->json('bookings');
        $this->assertCount(2, $bookings);

        // Gold booking assertions
        $goldBooking = collect($bookings)->firstWhere('metal', 'GOLD');
        $this->assertNotNull($goldBooking);
        $this->assertEquals('33000.00', $goldBooking['total_amount']);
        $this->assertEquals('2.000', $goldBooking['total_weight']);
        $this->assertEquals($requestToken, $goldBooking['booking_group_id']);
        $this->assertCount(1, $goldBooking['items']);
        $this->assertEquals('GOLD', $goldBooking['items'][0]['metal']);

        // Silver booking assertions
        $silverBooking = collect($bookings)->firstWhere('metal', 'SILVER');
        $this->assertNotNull($silverBooking);
        $this->assertEquals('1800.00', $silverBooking['total_amount']);
        $this->assertEquals('10.000', $silverBooking['total_weight']);
        $this->assertEquals($requestToken, $silverBooking['booking_group_id']);
        $this->assertCount(1, $silverBooking['items']);
        $this->assertEquals('SILVER', $silverBooking['items'][0]['metal']);

        // Verify different booking numbers
        $this->assertNotEquals($goldBooking['booking_no'], $silverBooking['booking_no']);

        // Verify database state
        $this->assertDatabaseHas('bhav_bookings', [
            'id' => $goldBooking['id'],
            'booking_group_id' => $requestToken,
            'metal' => 'GOLD',
            'total_amount' => 33000.00,
        ]);
        $this->assertDatabaseHas('bhav_bookings', [
            'id' => $silverBooking['id'],
            'booking_group_id' => $requestToken,
            'metal' => 'SILVER',
            'total_amount' => 1800.00,
        ]);
    }

    /**
     * b) Single-metal request still creates exactly 1 booking (old behaviour)
     * and the response is a one-item "bookings" array.
     */
    public function test_single_metal_request_creates_exactly_one_booking(): void
    {
        $customer = $this->createCustomer();
        $requestToken = Str::uuid()->toString();

        $payload = [
            'customer_id' => $customer->id,
            'request_token' => $requestToken,
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['metal' => 'GOLD', 'weight' => 2.000, 'rate' => 165000.00],
            ],
        ];

        $response = $this->postJson('/bhav-bookings', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'bookings',
            'group_id',
        ]);

        $bookings = $response->json('bookings');
        $this->assertCount(1, $bookings);
        $this->assertEquals('GOLD', $bookings[0]['metal']);
        $this->assertEquals('33000.00', $bookings[0]['total_amount']);
        $this->assertEquals($requestToken, $response->json('group_id'));

        // Backward compatibility check: id should also be accessible on root
        $this->assertEquals($bookings[0]['id'], $response->json('id'));
    }

    /**
     * c) Payment split: same booking as (a) with payments CASH 30,000 + ONLINE (UPI) 4,000
     * (total 34,000 <= 34,800) -> Gold booking payments: CASH 30,000 + ONLINE 3,000 (received 33,000);
     * Silver booking payments: ONLINE 1,000 (received 1,000).
     */
    public function test_payment_allocation_and_boundary_split_across_metals(): void
    {
        $customer = $this->createCustomer();
        $requestToken = Str::uuid()->toString();

        $payload = [
            'customer_id' => $customer->id,
            'request_token' => $requestToken,
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'items' => [
                ['metal' => 'GOLD', 'weight' => 2.000, 'rate' => 165000.00], // 33,000
                ['metal' => 'SILVER', 'weight' => 10.000, 'rate' => 1800.00], // 1,800 (Total: 34,800)
            ],
            'payments' => [
                ['mode' => 'CASH', 'amount' => 30000.00],
                ['mode' => 'ONLINE', 'online_method' => 'UPI', 'reference_no' => 'UPI999', 'amount' => 4000.00],
            ],
        ];

        $response = $this->postJson('/bhav-bookings', $payload);

        $response->assertStatus(201);
        $bookings = $response->json('bookings');
        $this->assertCount(2, $bookings);

        $goldBooking = collect($bookings)->firstWhere('metal', 'GOLD');
        $silverBooking = collect($bookings)->firstWhere('metal', 'SILVER');

        // Gold received_amount = 33000.00 (full amount received)
        $this->assertEquals('33000.00', $goldBooking['received_amount']);
        $this->assertCount(2, $goldBooking['payments']);
        $this->assertEquals('CASH', $goldBooking['payments'][0]['mode']);
        $this->assertEquals('30000.00', $goldBooking['payments'][0]['amount']);
        $this->assertEquals('ONLINE', $goldBooking['payments'][1]['mode']);
        $this->assertEquals('3000.00', $goldBooking['payments'][1]['amount']);
        $this->assertEquals('UPI', $goldBooking['payments'][1]['online_method']);

        // Silver received_amount = 1000.00 (from split boundary payment)
        $this->assertEquals('1000.00', $silverBooking['received_amount']);
        $this->assertCount(1, $silverBooking['payments']);
        $this->assertEquals('ONLINE', $silverBooking['payments'][0]['mode']);
        $this->assertEquals('1000.00', $silverBooking['payments'][0]['amount']);
        $this->assertEquals('UPI', $silverBooking['payments'][0]['online_method']);
    }

    /**
     * d) Payments total 35,000 (> 34,800) -> 422 and nothing saved.
     */
    public function test_payments_exceeding_combined_total_rejected_with_422(): void
    {
        $customer = $this->createCustomer();
        $requestToken = Str::uuid()->toString();

        $payload = [
            'customer_id' => $customer->id,
            'request_token' => $requestToken,
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'items' => [
                ['metal' => 'GOLD', 'weight' => 2.000, 'rate' => 165000.00], // 33,000
                ['metal' => 'SILVER', 'weight' => 10.000, 'rate' => 1800.00], // 1,800 (Total: 34,800)
            ],
            'payments' => [
                ['mode' => 'CASH', 'amount' => 35000.00],
            ],
        ];

        $response = $this->postJson('/bhav-bookings', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payments']);

        // Ensure nothing was saved in the database
        $this->assertDatabaseMissing('bhav_bookings', [
            'booking_group_id' => $requestToken,
        ]);
        $this->assertEquals(0, BhavBooking::count());
    }

    /**
     * e) Same request_token sent twice -> the same 2 bookings returned, no duplicates.
     */
    public function test_idempotency_returns_same_bookings_with_200(): void
    {
        $customer = $this->createCustomer();
        $requestToken = Str::uuid()->toString();

        $payload = [
            'customer_id' => $customer->id,
            'request_token' => $requestToken,
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'items' => [
                ['metal' => 'GOLD', 'weight' => 2.000, 'rate' => 165000.00],
                ['metal' => 'SILVER', 'weight' => 10.000, 'rate' => 1800.00],
            ],
        ];

        // First call: creates bookings (201)
        $firstResponse = $this->postJson('/bhav-bookings', $payload);
        $firstResponse->assertStatus(201);
        $this->assertEquals(2, BhavBooking::count());

        $firstBookings = $firstResponse->json('bookings');
        $firstIds = collect($firstBookings)->pluck('id')->sort()->values()->all();

        // Second call with identical request_token: returns existing bookings (200)
        $secondResponse = $this->postJson('/bhav-bookings', $payload);
        $secondResponse->assertStatus(200);

        // Ensure count in database did not increase
        $this->assertEquals(2, BhavBooking::count());

        $secondBookings = $secondResponse->json('bookings');
        $secondIds = collect($secondBookings)->pluck('id')->sort()->values()->all();

        $this->assertEquals($firstIds, $secondIds);
        $this->assertEquals($requestToken, $secondResponse->json('group_id'));
    }

    /**
     * f) Update still works for a single booking and rejects rows of a different metal (422).
     */
    public function test_update_single_booking_rejects_items_of_different_metal(): void
    {
        $customer = $this->createCustomer();

        // Create an initial GOLD booking
        $initialRes = $this->postJson('/bhav-bookings', [
            'customer_id' => $customer->id,
            'request_token' => Str::uuid()->toString(),
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['metal' => 'GOLD', 'weight' => 2.000, 'rate' => 165000.00],
            ],
        ]);
        $initialRes->assertStatus(201);
        $bookingId = $initialRes->json('id');

        // Attempt to update with a SILVER item -> must reject with 422
        $updatePayload = [
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['metal' => 'SILVER', 'weight' => 5.000, 'rate' => 1800.00],
            ],
        ];

        $updateRes = $this->putJson("/bhav-bookings/{$bookingId}", $updatePayload);
        $updateRes->assertStatus(422);
        $updateRes->assertJsonValidationErrors(['items.0.metal']);

        // Valid update with GOLD item works
        $validUpdatePayload = [
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'metal' => 'GOLD',
            'items' => [
                ['metal' => 'GOLD', 'weight' => 3.000, 'rate' => 165000.00],
            ],
        ];
        $validUpdateRes = $this->putJson("/bhav-bookings/{$bookingId}", $validUpdatePayload);
        $validUpdateRes->assertStatus(200);
        $this->assertEquals('49500.00', $validUpdateRes->json('total_amount'));
    }

    /**
     * Group print test: GET /bhav-bookings/group/{groupId}/print
     * Returns 200 with both bookings rendered on one page, 404 for unknown group.
     */
    public function test_group_print_route_displays_all_bookings_in_group(): void
    {
        $customer = $this->createCustomer();
        $requestToken = Str::uuid()->toString();

        $payload = [
            'customer_id' => $customer->id,
            'request_token' => $requestToken,
            'trade_type' => 'SELL',
            'entry_type' => 'ONLY_ENTRY',
            'items' => [
                ['metal' => 'GOLD', 'weight' => 2.000, 'rate' => 165000.00],
                ['metal' => 'SILVER', 'weight' => 10.000, 'rate' => 1800.00],
            ],
        ];

        $res = $this->postJson('/bhav-bookings', $payload);
        $res->assertStatus(201);

        $bookings = $res->json('bookings');
        $goldNo = $bookings[0]['booking_no'];
        $silverNo = $bookings[1]['booking_no'];

        // Test group print endpoint
        $printRes = $this->get("/bhav-bookings/group/{$requestToken}/print");
        $printRes->assertStatus(200);
        $printRes->assertSee($goldNo);
        $printRes->assertSee($silverNo);
        $printRes->assertSee('GOLD');
        $printRes->assertSee('SILVER');
        $printRes->assertSee('₹33,000.00');
        $printRes->assertSee('₹1,800.00');

        // Test non-existent group ID returns 404
        $notFoundRes = $this->get('/bhav-bookings/group/non-existent-uuid/print');
        $notFoundRes->assertStatus(404);
    }
}
