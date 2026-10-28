<?php

namespace Tests\Feature;

use App\Models\DailyRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyRateTrendTest extends TestCase
{
    use RefreshDatabase;

    public function test_today_rates_returns_previous_rate_trend_and_changes(): void
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Previous day rate: Gold 160000, Silver 1900
        DailyRate::create([
            'metal' => 'GOLD',
            'rate' => 160000,
            'rate_date' => $yesterday,
        ]);
        DailyRate::create([
            'metal' => 'SILVER',
            'rate' => 1900,
            'rate_date' => $yesterday,
        ]);

        // Today rate: Gold 165000 (+5000, +3.13%), Silver 1800 (-100, -5.26%)
        DailyRate::create([
            'metal' => 'GOLD',
            'rate' => 165000,
            'rate_date' => $today,
        ]);
        DailyRate::create([
            'metal' => 'SILVER',
            'rate' => 1800,
            'rate_date' => $today,
        ]);

        $response = $this->getJson('/daily-rates/today');

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertTrue($data['GOLD']['is_set']);
        $this->assertEquals(165000.0, $data['GOLD']['rate']);
        $this->assertEquals(160000.0, $data['GOLD']['previous_rate']);
        $this->assertEquals(5000.0, $data['GOLD']['change']);
        $this->assertEquals(3.13, $data['GOLD']['percent']);

        $this->assertTrue($data['SILVER']['is_set']);
        $this->assertEquals(1800.0, $data['SILVER']['rate']);
        $this->assertEquals(1900.0, $data['SILVER']['previous_rate']);
        $this->assertEquals(-100.0, $data['SILVER']['change']);
        $this->assertEquals(-5.26, $data['SILVER']['percent']);
    }

    public function test_today_rates_returns_null_trend_when_today_not_set_and_previous_exists(): void
    {
        $yesterday = now()->subDay()->toDateString();

        // Previous day rate: Gold 160000, Silver 1900
        DailyRate::create([
            'metal' => 'GOLD',
            'rate' => 160000,
            'rate_date' => $yesterday,
        ]);
        DailyRate::create([
            'metal' => 'SILVER',
            'rate' => 1900,
            'rate_date' => $yesterday,
        ]);

        // Today rate is NOT set
        $response = $this->getJson('/daily-rates/today');

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertFalse($data['GOLD']['is_set']);
        $this->assertEquals((float) config('bhav.default_rates.GOLD'), $data['GOLD']['rate']);
        $this->assertEquals(160000.0, $data['GOLD']['previous_rate']);
        $this->assertNull($data['GOLD']['change']);
        $this->assertNull($data['GOLD']['percent']);

        $this->assertFalse($data['SILVER']['is_set']);
        $this->assertEquals((float) config('bhav.default_rates.SILVER'), $data['SILVER']['rate']);
        $this->assertEquals(1900.0, $data['SILVER']['previous_rate']);
        $this->assertNull($data['SILVER']['change']);
        $this->assertNull($data['SILVER']['percent']);
    }

    public function test_today_rates_handles_no_previous_rate(): void
    {
        $today = now()->toDateString();

        // Only today rate set
        DailyRate::create([
            'metal' => 'GOLD',
            'rate' => 170000,
            'rate_date' => $today,
        ]);

        $response = $this->getJson('/daily-rates/today');

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertTrue($data['GOLD']['is_set']);
        $this->assertEquals(170000.0, $data['GOLD']['rate']);
        $this->assertNull($data['GOLD']['previous_rate']);
        $this->assertNull($data['GOLD']['change']);
        $this->assertNull($data['GOLD']['percent']);
    }
}

