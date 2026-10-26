<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BhavBookingListQATest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(string $name, string $mobile): Customer
    {
        return Customer::create([
            'customer_no' => 'CUST-' . rand(1000, 9999),
            'name'        => $name,
            'mobile'      => $mobile,
            'party_type'  => 'CUSTOMER',
            'city'        => 'Indore',
        ]);
    }

    private function seedDiverseBookings(): array
    {
        $c1 = $this->createCustomer('Ramesh Gold', '9826011111');
        $c2 = $this->createCustomer('Suresh Silver', '9826022222');

        // B1: Customer 1, SELL, GOLD, ONLY_ENTRY, PENDING, Today, 10gm @ 70,000 = 70,000
        $b1 = BhavBooking::create([
            'booking_no'       => 'BB-000001',
            'customer_id'      => $c1->id,
            'request_token'    => Str::uuid()->toString(),
            'trade_type'       => 'SELL',
            'entry_type'       => 'ONLY_ENTRY',
            'metal'            => 'GOLD',
            'status'           => 'PENDING',
            'total_weight'     => 10.000,
            'delivered_weight' => 0.000,
            'total_amount'     => 70000.00,
            'received_amount'  => 10000.00,
            'given_amount'     => 0.00,
            'booked_at'        => now(),
        ]);

        // B2: Customer 2, BUY, SILVER, BHAV_CUT_LEDGER, PENDING, Yesterday, 1000gm @ 85.00/10gm = 8,500
        $b2 = BhavBooking::create([
            'booking_no'       => 'BB-000002',
            'customer_id'      => $c2->id,
            'request_token'    => Str::uuid()->toString(),
            'trade_type'       => 'BUY',
            'entry_type'       => 'BHAV_CUT_LEDGER',
            'metal'            => 'SILVER',
            'status'           => 'PENDING',
            'total_weight'     => 1000.000,
            'delivered_weight' => 0.000,
            'total_amount'     => 8500.00,
            'received_amount'  => 0.00,
            'given_amount'     => 5000.00,
            'booked_at'        => now()->subDay(),
        ]);

        // B3: Customer 1, BUY, GOLD, ONLY_ENTRY, DELIVERED (Old view), 5 days ago, 5gm @ 70,000 = 35,000
        $b3 = BhavBooking::create([
            'booking_no'       => 'BB-000003',
            'customer_id'      => $c1->id,
            'request_token'    => Str::uuid()->toString(),
            'trade_type'       => 'BUY',
            'entry_type'       => 'ONLY_ENTRY',
            'metal'            => 'GOLD',
            'status'           => 'DELIVERED',
            'total_weight'     => 5.000,
            'delivered_weight' => 5.000,
            'total_amount'     => 35000.00,
            'received_amount'  => 0.00,
            'given_amount'     => 35000.00,
            'booked_at'        => now()->subDays(5),
        ]);

        return [$c1, $c2, $b1, $b2, $b3];
    }

    public function test_list_filter_by_party_name_mobile_or_booking_no(): void
    {
        [$c1, $c2, $b1, $b2, $b3] = $this->seedDiverseBookings();

        // 1. Filter by customer name
        $resName = $this->getJson('/bhav-bookings/list?party=Ramesh');
        $resName->assertStatus(200);
        $this->assertCount(1, $resName->json('rows'));
        $this->assertEquals($b1->id, $resName->json('rows.0.id'));

        // 2. Filter by mobile
        $resMobile = $this->getJson('/bhav-bookings/list?party=9826022222');
        $resMobile->assertStatus(200);
        $this->assertCount(1, $resMobile->json('rows'));
        $this->assertEquals($b2->id, $resMobile->json('rows.0.id'));

        // 3. Filter by booking_no
        $resBNo = $this->getJson('/bhav-bookings/list?party=BB-000001');
        $resBNo->assertStatus(200);
        $this->assertCount(1, $resBNo->json('rows'));
        $this->assertEquals($b1->id, $resBNo->json('rows.0.id'));
    }

    public function test_list_filter_by_date_range(): void
    {
        [$c1, $c2, $b1, $b2, $b3] = $this->seedDiverseBookings();

        // Filter only today
        $today = now()->format('Y-m-d');
        $resToday = $this->getJson("/bhav-bookings/list?date_from={$today}&date_to={$today}");
        $resToday->assertStatus(200);
        $this->assertCount(1, $resToday->json('rows'));
        $this->assertEquals($b1->id, $resToday->json('rows.0.id'));
    }

    public function test_list_filter_by_metal(): void
    {
        [$c1, $c2, $b1, $b2, $b3] = $this->seedDiverseBookings();

        // Filter GOLD (current view has B1)
        $resGold = $this->getJson('/bhav-bookings/list?metal=GOLD');
        $resGold->assertStatus(200);
        $this->assertCount(1, $resGold->json('rows'));
        $this->assertEquals($b1->id, $resGold->json('rows.0.id'));

        // Filter SILVER (current view has B2)
        $resSilver = $this->getJson('/bhav-bookings/list?metal=SILVER');
        $resSilver->assertStatus(200);
        $this->assertCount(1, $resSilver->json('rows'));
        $this->assertEquals($b2->id, $resSilver->json('rows.0.id'));
    }

    public function test_list_filter_by_entry_type(): void
    {
        [$c1, $c2, $b1, $b2, $b3] = $this->seedDiverseBookings();

        $resLedger = $this->getJson('/bhav-bookings/list?entry_type=BHAV_CUT_LEDGER');
        $resLedger->assertStatus(200);
        $this->assertCount(1, $resLedger->json('rows'));
        $this->assertEquals($b2->id, $resLedger->json('rows.0.id'));
    }

    public function test_list_filter_by_trade_type(): void
    {
        [$c1, $c2, $b1, $b2, $b3] = $this->seedDiverseBookings();

        $resBuy = $this->getJson('/bhav-bookings/list?trade_type=BUY');
        $resBuy->assertStatus(200);
        $this->assertCount(1, $resBuy->json('rows'));
        $this->assertEquals($b2->id, $resBuy->json('rows.0.id'));

        $resSell = $this->getJson('/bhav-bookings/list?trade_type=SELL');
        $resSell->assertStatus(200);
        $this->assertCount(1, $resSell->json('rows'));
        $this->assertEquals($b1->id, $resSell->json('rows.0.id'));
    }

    public function test_list_filter_by_view_current_and_old(): void
    {
        [$c1, $c2, $b1, $b2, $b3] = $this->seedDiverseBookings();

        // Default current view: PENDING/PARTIAL -> B1, B2
        $resCurrent = $this->getJson('/bhav-bookings/list?view=current');
        $resCurrent->assertStatus(200);
        $this->assertCount(2, $resCurrent->json('rows'));

        // Old view: DELIVERED/CANCELLED -> B3
        $resOld = $this->getJson('/bhav-bookings/list?view=old');
        $resOld->assertStatus(200);
        $this->assertCount(1, $resOld->json('rows'));
        $this->assertEquals($b3->id, $resOld->json('rows.0.id'));
    }

    public function test_list_per_page_allowed_values_only_10_50_100(): void
    {
        // Allowed: 10, 50, 100
        $res10 = $this->getJson('/bhav-bookings/list?per_page=10');
        $res10->assertStatus(200);

        $res50 = $this->getJson('/bhav-bookings/list?per_page=50');
        $res50->assertStatus(200);

        $res100 = $this->getJson('/bhav-bookings/list?per_page=100');
        $res100->assertStatus(200);

        // Invalid: 25 -> 422
        $resInvalid = $this->getJson('/bhav-bookings/list?per_page=25');
        $resInvalid->assertStatus(422);
        $resInvalid->assertJsonValidationErrors(['per_page']);
    }

    public function test_list_combined_filters(): void
    {
        [$c1, $c2, $b1, $b2, $b3] = $this->seedDiverseBookings();

        // Current view + metal GOLD + trade_type SELL + party Ramesh
        $res = $this->getJson('/bhav-bookings/list?view=current&metal=GOLD&trade_type=SELL&party=Ramesh');
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('rows'));
        $this->assertEquals($b1->id, $res->json('rows.0.id'));
    }

    public function test_list_totals_are_for_whole_filtered_result_and_gold_silver_weights_never_mixed(): void
    {
        [$c1, $c2, $b1, $b2, $b3] = $this->seedDiverseBookings();

        // Current view has B1 (Gold 10gm, SELL, 70k, received 10k) and B2 (Silver 1000gm, BUY, 8.5k, given 5k)
        $res = $this->getJson('/bhav-bookings/list?view=current');
        $res->assertStatus(200);
        $totals = $res->json('totals');

        // Gold and silver weights MUST NOT be added together into a single number
        $this->assertEquals(0.000, $totals['buy_weight_gold']);
        $this->assertEquals(1000.000, $totals['buy_weight_silver']);
        $this->assertEquals(8500.00, $totals['buy_amount']);

        $this->assertEquals(10.000, $totals['sell_weight_gold']);
        $this->assertEquals(0.000, $totals['sell_weight_silver']);
        $this->assertEquals(70000.00, $totals['sell_amount']);

        $this->assertEquals(5000.00, $totals['given_total']);
        $this->assertEquals(10000.00, $totals['received_total']);
    }

    public function test_list_pagination_from_to_is_correct(): void
    {
        $customer = $this->createCustomer('Pagination Target', '9826033333');

        // Create 15 bookings
        for ($i = 1; $i <= 15; $i++) {
            BhavBooking::create([
                'booking_no'       => sprintf('BB-%06d', $i),
                'customer_id'      => $customer->id,
                'request_token'    => Str::uuid()->toString(),
                'trade_type'       => 'SELL',
                'entry_type'       => 'ONLY_ENTRY',
                'metal'            => 'GOLD',
                'status'           => 'PENDING',
                'total_weight'     => 1.000,
                'delivered_weight' => 0.000,
                'total_amount'     => 7000.00,
                'received_amount'  => 0.00,
                'given_amount'     => 0.00,
                'booked_at'        => now()->subMinutes(15 - $i),
            ]);
        }

        // Page 1 with per_page 10 -> from: 1, to: 10, total: 15
        $resP1 = $this->getJson('/bhav-bookings/list?per_page=10&page=1');
        $resP1->assertStatus(200);
        $p1 = $resP1->json('pagination');
        $this->assertEquals(1, $p1['from']);
        $this->assertEquals(10, $p1['to']);
        $this->assertEquals(15, $p1['total']);

        // Page 2 with per_page 10 -> from: 11, to: 15, total: 15
        $resP2 = $this->getJson('/bhav-bookings/list?per_page=10&page=2');
        $resP2->assertStatus(200);
        $p2 = $resP2->json('pagination');
        $this->assertEquals(11, $p2['from']);
        $this->assertEquals(15, $p2['to']);
        $this->assertEquals(15, $p2['total']);
    }
}
