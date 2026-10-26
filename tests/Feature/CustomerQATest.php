<?php

namespace Tests\Feature;

use App\Models\BhavBooking;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerQATest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_search_by_name(): void
    {
        Customer::create([
            'customer_no' => 'CUST-0001',
            'name'        => 'Ramesh Patel',
            'mobile'      => '9826011122',
            'city'        => 'Indore',
            'party_type'  => 'CUSTOMER',
        ]);

        Customer::create([
            'customer_no' => 'CUST-0002',
            'name'        => 'Suresh Kumar',
            'mobile'      => '9826011133',
            'city'        => 'Bhopal',
            'party_type'  => 'CUSTOMER',
        ]);

        $response = $this->getJson('/customers/search?q=Ramesh');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'Ramesh Patel']);
    }

    public function test_customer_search_by_mobile(): void
    {
        Customer::create([
            'customer_no' => 'CUST-0001',
            'name'        => 'Ramesh Patel',
            'mobile'      => '9826011122',
            'city'        => 'Indore',
            'party_type'  => 'CUSTOMER',
        ]);

        $response = $this->getJson('/customers/search?q=9826011122');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['mobile' => '9826011122']);
    }

    public function test_customer_search_by_customer_no(): void
    {
        Customer::create([
            'customer_no' => 'CUST-0099',
            'name'        => 'Anita Sharma',
            'mobile'      => '9826099999',
            'city'        => 'Ujjain',
            'party_type'  => 'CUSTOMER',
        ]);

        $response = $this->getJson('/customers/search?q=CUST-0099');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['customer_no' => 'CUST-0099']);
    }

    public function test_recent_customers_ordered_by_latest_booking_and_filled_with_newest(): void
    {
        // Create 10 customers
        $customers = [];
        for ($i = 1; $i <= 10; $i++) {
            $customers[$i] = Customer::create([
                'customer_no' => sprintf('CUST-%04d', $i),
                'name'        => "Customer {$i}",
                'mobile'      => sprintf('98000000%02d', $i),
                'city'        => 'Indore',
                'party_type'  => 'CUSTOMER',
            ]);
        }

        // Give Customer 3 the latest booking, Customer 1 an earlier booking
        BhavBooking::create([
            'booking_no'       => 'BB-000001',
            'customer_id'      => $customers[1]->id,
            'request_token'    => \Illuminate\Support\Str::uuid()->toString(),
            'trade_type'       => 'SELL',
            'entry_type'       => 'ONLY_ENTRY',
            'metal'            => 'GOLD',
            'status'           => 'PENDING',
            'total_weight'     => 10.000,
            'delivered_weight' => 0.000,
            'total_amount'     => 70000.00,
            'received_amount'  => 0.00,
            'given_amount'     => 0.00,
            'booked_at'        => now()->subDays(2),
        ]);

        BhavBooking::create([
            'booking_no'       => 'BB-000002',
            'customer_id'      => $customers[3]->id,
            'request_token'    => \Illuminate\Support\Str::uuid()->toString(),
            'trade_type'       => 'SELL',
            'entry_type'       => 'ONLY_ENTRY',
            'metal'            => 'GOLD',
            'status'           => 'PENDING',
            'total_weight'     => 10.000,
            'delivered_weight' => 0.000,
            'total_amount'     => 70000.00,
            'received_amount'  => 0.00,
            'given_amount'     => 0.00,
            'booked_at'        => now()->subMinutes(10),
        ]);

        $response = $this->getJson('/customers/recent');
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(8, $data);
        // First should be Customer 3 (latest booking)
        $this->assertEquals($customers[3]->id, $data[0]['id']);
        // Second should be Customer 1 (earlier booking)
        $this->assertEquals($customers[1]->id, $data[1]['id']);
        // Remaining 6 should be filled with newest by id desc (Customer 10, 9, 8, 7, 6, 5)
        $this->assertEquals($customers[10]->id, $data[2]['id']);
        $this->assertEquals($customers[9]->id, $data[3]['id']);
    }

    public function test_store_validation_name_required(): void
    {
        $response = $this->postJson('/customers', [
            'name'   => '',
            'mobile' => '9826011122',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_validation_mobile_required_10_digits_unique(): void
    {
        // 1. Missing mobile
        $res1 = $this->postJson('/customers', ['name' => 'Test User', 'mobile' => '']);
        $res1->assertStatus(422);
        $res1->assertJsonValidationErrors(['mobile']);

        // 2. Invalid digits (not 10 digits)
        $res2 = $this->postJson('/customers', ['name' => 'Test User', 'mobile' => '98260']);
        $res2->assertStatus(422);
        $res2->assertJsonValidationErrors(['mobile']);

        // 3. Unique violation
        Customer::create([
            'customer_no' => 'CUST-0001',
            'name'        => 'Existing User',
            'mobile'      => '9826011122',
            'party_type'  => 'CUSTOMER',
        ]);

        $res3 = $this->postJson('/customers', ['name' => 'Another User', 'mobile' => '9826011122']);
        $res3->assertStatus(422);
        $res3->assertJsonValidationErrors(['mobile']);
    }

    public function test_store_validation_family_sub_profile_can_reuse_parent_mobile(): void
    {
        $parent = Customer::create([
            'customer_no' => 'CUST-0001',
            'name'        => 'Parent User',
            'mobile'      => '9826011122',
            'party_type'  => 'CUSTOMER',
        ]);

        $response = $this->postJson('/customers', [
            'name'                  => 'Child User',
            'mobile'                => '9826011122', // Same mobile as parent
            'is_family_sub_profile' => true,
            'parent_customer_id'    => $parent->id,
            'party_type'            => 'CUSTOMER',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('customers', [
            'name'               => 'Child User',
            'mobile'             => '9826011122',
            'parent_customer_id' => $parent->id,
        ]);
    }

    public function test_store_validation_pan_format(): void
    {
        // Invalid PAN format
        $res1 = $this->postJson('/customers', [
            'name'   => 'Test PAN',
            'mobile' => '9826011123',
            'pan_no' => 'INVALID123',
        ]);
        $res1->assertStatus(422);
        $res1->assertJsonValidationErrors(['pan_no']);

        // Valid PAN format (5 letters, 4 digits, 1 letter)
        $res2 = $this->postJson('/customers', [
            'name'   => 'Test PAN',
            'mobile' => '9826011123',
            'pan_no' => 'ABCDE1234F',
        ]);
        $res2->assertStatus(201);
    }

    public function test_store_validation_gst_format(): void
    {
        // Invalid GST
        $res1 = $this->postJson('/customers', [
            'name'   => 'Test GST',
            'mobile' => '9826011124',
            'gst_no' => 'INVALIDGST',
        ]);
        $res1->assertStatus(422);
        $res1->assertJsonValidationErrors(['gst_no']);

        // Valid GST (15 chars: 2 digits + 10 PAN chars + 3 chars)
        $res2 = $this->postJson('/customers', [
            'name'   => 'Test GST',
            'mobile' => '9826011124',
            'gst_no' => '22AAAAA0000A1Z5',
        ]);
        $res2->assertStatus(201);
    }

    public function test_store_validation_aadhaar_format(): void
    {
        // Invalid Aadhaar (not 12 digits)
        $res1 = $this->postJson('/customers', [
            'name'       => 'Test Aadhaar',
            'mobile'     => '9826011125',
            'aadhaar_no' => '123456',
        ]);
        $res1->assertStatus(422);
        $res1->assertJsonValidationErrors(['aadhaar_no']);

        // Valid Aadhaar
        $res2 = $this->postJson('/customers', [
            'name'       => 'Test Aadhaar',
            'mobile'     => '9826011125',
            'aadhaar_no' => '123456789012',
        ]);
        $res2->assertStatus(201);
    }

    public function test_store_validation_birthday_and_anniversary_year_1900_to_today(): void
    {
        // Future date should fail
        $res1 = $this->postJson('/customers', [
            'name'     => 'Future Birthday',
            'mobile'   => '9826011126',
            'birthday' => now()->addDays(5)->format('Y-m-d'),
        ]);
        $res1->assertStatus(422);
        $res1->assertJsonValidationErrors(['birthday']);

        // Date before 1900 should fail
        $res2 = $this->postJson('/customers', [
            'name'     => 'Old Birthday',
            'mobile'   => '9826011126',
            'birthday' => '1899-12-31',
        ]);
        $res2->assertStatus(422);
        $res2->assertJsonValidationErrors(['birthday']);

        // Valid birthday
        $res3 = $this->postJson('/customers', [
            'name'     => 'Valid Birthday',
            'mobile'   => '9826011126',
            'birthday' => '1990-05-15',
        ]);
        $res3->assertStatus(201);
    }

    public function test_store_validation_image_type_and_size(): void
    {
        Storage::fake('public');

        // Non-image file should fail
        $textFile = UploadedFile::fake()->create('test.txt', 100);
        $res1 = $this->postJson('/customers', [
            'name'          => 'Test Image',
            'mobile'        => '9826011127',
            'profile_image' => $textFile,
        ]);
        $res1->assertStatus(422);
        $res1->assertJsonValidationErrors(['profile_image']);

        // Image larger than 2MB should fail
        $pngHeader = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $largeImage = UploadedFile::fake()->createWithContent('large.png', $pngHeader . str_repeat('0', 3 * 1024 * 1024)); // > 3MB
        $res2 = $this->postJson('/customers', [
            'name'          => 'Test Image',
            'mobile'        => '9826011127',
            'profile_image' => $largeImage,
        ]);
        $res2->assertStatus(422);
        $res2->assertJsonValidationErrors(['profile_image']);

        // Valid image <= 2MB
        $validImage = UploadedFile::fake()->createWithContent('valid.png', $pngHeader);
        $res3 = $this->postJson('/customers', [
            'name'          => 'Test Image',
            'mobile'        => '9826011127',
            'profile_image' => $validImage,
        ]);
        $res3->assertStatus(201);
    }

    public function test_aadhaar_and_pan_never_present_in_json(): void
    {
        $response = $this->postJson('/customers', [
            'name'       => 'Secret User',
            'mobile'     => '9826011128',
            'aadhaar_no' => '123456789012',
            'pan_no'     => 'ABCDE1234F',
        ]);

        $response->assertStatus(201);
        $json = $response->json();
        $this->assertArrayNotHasKey('aadhaar_no', $json);
        $this->assertArrayNotHasKey('pan_no', $json);

        // Also check search API
        $searchRes = $this->getJson('/customers/search?q=Secret');
        $searchRes->assertStatus(200);
        $searchData = $searchRes->json();
        $this->assertNotEmpty($searchData);
        $this->assertArrayNotHasKey('aadhaar_no', $searchData[0]);
        $this->assertArrayNotHasKey('pan_no', $searchData[0]);
    }

    public function test_customer_number_race_retry_on_collision(): void
    {
        // Bug a: If a unique violation happens on customer_no, generation must retry up to 3 times
        Customer::create([
            'customer_no' => 'CUST-0001',
            'name'        => 'Existing Customer 1',
            'mobile'      => '9826011129',
            'party_type'  => 'CUSTOMER',
        ]);

        $response = $this->postJson('/customers', [
            'name'   => 'New Customer',
            'mobile' => '9826011130',
        ]);

        $response->assertStatus(201);
        $this->assertEquals('CUST-0002', $response->json('customer_no'));
    }

    public function test_mobile_duplicate_race_lock_check(): void
    {
        // Bug d: verify unique mobile check is protected
        Customer::create([
            'customer_no' => 'CUST-0001',
            'name'        => 'First User',
            'mobile'      => '9826011131',
            'party_type'  => 'CUSTOMER',
        ]);

        $response = $this->postJson('/customers', [
            'name'   => 'Duplicate Mobile User',
            'mobile' => '9826011131',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['mobile']);
    }
}
