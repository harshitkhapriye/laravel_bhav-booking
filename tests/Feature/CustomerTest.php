<?php

namespace Tests\Feature;

use App\Models\Customer;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    /**
     * Test empty query returns empty JSON array.
     */
    public function test_customer_search_empty_query_returns_empty_array(): void
    {
        $response = $this->getJson('/customers/search?q=');
        $response->assertStatus(200);
        $response->assertJson([]);
    }

    /**
     * Test search by name.
     */
    public function test_customer_search_by_name(): void
    {
        $response = $this->getJson('/customers/search?q=Ramesh');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Ramesh Patel',
            'city' => 'Indore',
        ]);
    }

    /**
     * Test search by mobile.
     */
    public function test_customer_search_by_mobile(): void
    {
        $response = $this->getJson('/customers/search?q=9826011122');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'customer_no' => 'CUST-0001',
            'mobile' => '9826011122',
        ]);
    }

    /**
     * Test search by customer_no.
     */
    public function test_customer_search_by_customer_no(): void
    {
        $response = $this->getJson('/customers/search?q=CUST-0002');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'customer_no' => 'CUST-0002',
            'name' => 'Sunita Sharma',
        ]);
    }

    /**
     * Test customer store with validation failure (missing name).
     */
    public function test_customer_store_validation_failure(): void
    {
        $response = $this->postJson('/customers', [
            'name' => '',
            'mobile' => '12345', // invalid digits
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'mobile']);
    }

    /**
     * Test successful customer creation with auto-generated customer_no.
     */
    public function test_customer_store_successful(): void
    {
        $uniqueMobile = '99999' . rand(10000, 99999);
        $response = $this->postJson('/customers', [
            'name' => 'Test Automated Customer',
            'mobile' => $uniqueMobile,
            'city' => 'Indore',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => 'Test Automated Customer',
            'mobile' => $uniqueMobile,
            'city' => 'Indore',
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data['customer_no']);
        $this->assertMatchesRegularExpression('/^CUST-\d{4}$/', $data['customer_no']);
    }

    /**
     * Test Bhav Booking page loads with 200.
     */
    public function test_bhav_booking_page_loads(): void
    {
        $response = $this->get('/buy-sell/bhav-booking');
        $response->assertStatus(200);
        $response->assertSee('Bhav Booking');
        $response->assertSee('customerSearchInput');
        $response->assertSee('addCustomerModal');
    }
}
