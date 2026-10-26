<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\BhavBookingLog;
use App\Models\Customer;
use App\Models\DailyRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BhavBookingImprovementsTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::create([
            'customer_no' => 'CUST-0001',
            'name'        => 'Test Customer',
            'mobile'      => '9826012345',
            'city'        => 'Indore',
            'party_type'  => 'CUSTOMER',
        ]);
    }

    /**
     * Test 1: GET /daily-rates/today returns default rates when not set.
     */
    public function test_get_today_rates_returns_defaults_when_not_set(): void
    {
        $response = $this->getJson('/daily-rates/today');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertArrayHasKey('GOLD', $data);
        $this->assertArrayHasKey('SILVER', $data);
        $this->assertFalse($data['GOLD']['is_set']);
        $this->assertFalse($data['SILVER']['is_set']);
        $this->assertEquals(config('bhav.default_rates.GOLD'), $data['GOLD']['rate']);
        $this->assertEquals(config('bhav.default_rates.SILVER'), $data['SILVER']['rate']);
    }

    /**
     * Test 2: POST /daily-rates stores today's rates and validates > 0.
     */
    public function test_store_today_rates_validates_and_stores(): void
    {
        // Rejects 0 or negative
        $resInvalid = $this->postJson('/daily-rates', [
            'gold_rate'   => 0,
            'silver_rate' => -100,
        ]);
        $resInvalid->assertStatus(422);

        // Valid rates
        $resValid = $this->postJson('/daily-rates', [
            'gold_rate'   => 170000.50,
            'silver_rate' => 1950.00,
        ]);
        $resValid->assertStatus(200);
        $this->assertEquals(170000.50, $resValid->json('rates.GOLD.rate'));
        $this->assertTrue($resValid->json('rates.GOLD.is_set'));
        $this->assertEquals(1950.00, $resValid->json('rates.SILVER.rate'));
        $this->assertTrue($resValid->json('rates.SILVER.is_set'));

        // Verify GET /daily-rates/today reflects the updated values
        $resToday = $this->getJson('/daily-rates/today');
        $resToday->assertStatus(200);
        $this->assertEquals(170000.50, $resToday->json('GOLD.rate'));
        $this->assertTrue($resToday->json('GOLD.is_set'));
    }

    /**
     * Test 3: Activity logs created automatically on booking create, update, delete.
     */
    public function test_activity_logs_created_on_booking_lifecycle(): void
    {
        // 1. Create booking
        $createRes = $this->postJson('/bhav-bookings', [
            'request_token' => (string) Str::uuid(),
            'customer_id'   => $this->customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [
                ['weight' => 2.000, 'rate' => 150000.00],
            ],
            'payments'      => [
                ['mode' => 'CASH', 'amount' => 10000.00],
            ],
        ]);
        $createRes->assertStatus(201);
        $bookingId = $createRes->json('id');

        // Verify CREATED log
        $createLog = BhavBookingLog::where('bhav_booking_id', $bookingId)
            ->where('action', 'CREATED')
            ->first();
        $this->assertNotNull($createLog);
        $this->assertStringContainsString('Created booking', $createLog->summary);

        // 2. Update booking
        $updateRes = $this->putJson("/bhav-bookings/{$bookingId}", [
            'customer_id' => $this->customer->id,
            'trade_type'  => 'SELL',
            'entry_type'  => 'ONLY_ENTRY',
            'metal'       => 'GOLD',
            'items'       => [
                ['weight' => 3.000, 'rate' => 150000.00],
            ],
            'payments'    => [
                ['mode' => 'CASH', 'amount' => 10000.00],
            ],
        ]);
        $updateRes->assertStatus(200);

        // Verify UPDATED log
        $updateLog = BhavBookingLog::where('bhav_booking_id', $bookingId)
            ->where('action', 'UPDATED')
            ->first();
        $this->assertNotNull($updateLog);
        $this->assertStringContainsString('Weight', $updateLog->summary);

        // 3. Log external action (PRINTED)
        $printLogRes = $this->postJson("/bhav-bookings/{$bookingId}/log-action", [
            'action' => 'PRINTED',
        ]);
        $printLogRes->assertStatus(200);

        // 4. Soft delete
        $delRes = $this->deleteJson("/bhav-bookings/{$bookingId}");
        $delRes->assertStatus(200);

        // Verify DELETED log
        $delLog = BhavBookingLog::where('bhav_booking_id', $bookingId)
            ->where('action', 'DELETED')
            ->first();
        $this->assertNotNull($delLog);
    }

    /**
     * Test 4: View booking includes activity logs with summary.
     */
    public function test_show_booking_includes_activity_logs(): void
    {
        $createRes = $this->postJson('/bhav-bookings', [
            'request_token' => (string) Str::uuid(),
            'customer_id'   => $this->customer->id,
            'trade_type'    => 'BUY',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'SILVER',
            'items'         => [
                ['weight' => 500.000, 'rate' => 1800.00],
            ],
        ]);
        $createRes->assertStatus(201);
        $bookingId = $createRes->json('id');

        $showRes = $this->getJson("/bhav-bookings/{$bookingId}");
        $showRes->assertStatus(200);
        $showRes->assertJsonStructure([
            'id',
            'booking_no',
            'logs' => [
                '*' => ['id', 'action', 'summary', 'created_at'],
            ],
        ]);
        $this->assertNotEmpty($showRes->json('logs'));
    }

    /**
     * Test 5: CSV Export streams response with filtered rows and payments.
     */
    public function test_export_csv_streams_filtered_bookings(): void
    {
        // Create 2 bookings (one Gold, one Silver)
        $this->postJson('/bhav-bookings', [
            'request_token' => (string) Str::uuid(),
            'customer_id'   => $this->customer->id,
            'trade_type'    => 'SELL',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'GOLD',
            'items'         => [
                ['weight' => 2.000, 'rate' => 150000.00],
            ],
            'payments'      => [
                ['mode' => 'CASH', 'amount' => 5000.00],
            ],
        ]);

        $this->postJson('/bhav-bookings', [
            'request_token' => (string) Str::uuid(),
            'customer_id'   => $this->customer->id,
            'trade_type'    => 'BUY',
            'entry_type'    => 'ONLY_ENTRY',
            'metal'         => 'SILVER',
            'items'         => [
                ['weight' => 100.000, 'rate' => 1800.00],
            ],
        ]);

        // Export all
        $resAll = $this->get('/bhav-bookings/export?view=current');
        $resAll->assertStatus(200);
        $resAll->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename="bhav_bookings_export_', (string) $resAll->headers->get('Content-Disposition'));

        // Stream content assertion
        ob_start();
        $resAll->sendContent();
        $contentAll = ob_get_clean();

        $this->assertStringContainsString('Booking No', $contentAll);
        $this->assertStringContainsString('Customer No', $contentAll);
        $this->assertStringContainsString('GOLD', $contentAll);
        $this->assertStringContainsString('SILVER', $contentAll);
        $this->assertStringContainsString('CASH: ₹5000', $contentAll);

        // Export filtered by metal = GOLD only
        $resGold = $this->get('/bhav-bookings/export?view=current&metal=GOLD');
        ob_start();
        $resGold->sendContent();
        $contentGold = ob_get_clean();

        $this->assertStringContainsString('GOLD', $contentGold);
        $this->assertStringNotContainsString('SILVER', $contentGold);
    }
}
