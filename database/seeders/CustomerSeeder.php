<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds with 20 realistic Indian customers.
     */
    public function run(): void
    {
        $customers = [
            [
                'customer_no' => 'CUST-0001',
                'name' => 'Ramesh Patel',
                'mobile' => '9826011122',
                'city' => 'Indore',
            ],
            [
                'customer_no' => 'CUST-0002',
                'name' => 'Sunita Sharma',
                'mobile' => '9893022233',
                'city' => 'Bhopal',
            ],
            [
                'customer_no' => 'CUST-0003',
                'name' => 'Vikram Mehta',
                'mobile' => '9755033344',
                'city' => 'Ujjain',
            ],
            [
                'customer_no' => 'CUST-0004',
                'name' => 'Rajesh Verma',
                'mobile' => '9425044455',
                'city' => 'Indore',
            ],
            [
                'customer_no' => 'CUST-0005',
                'name' => 'Anil Jain',
                'mobile' => '9827055566',
                'city' => 'Dewas',
            ],
            [
                'customer_no' => 'CUST-0006',
                'name' => 'Pooja Agarwal',
                'mobile' => '9893166677',
                'city' => 'Bhopal',
            ],
            [
                'customer_no' => 'CUST-0007',
                'name' => 'Priya Joshi',
                'mobile' => '9424077788',
                'city' => 'Ujjain',
            ],
            [
                'customer_no' => 'CUST-0008',
                'name' => 'Deepak Gupta',
                'mobile' => '9826288899',
                'city' => 'Indore',
            ],
            [
                'customer_no' => 'CUST-0009',
                'name' => 'Suresh Soni',
                'mobile' => '9893399900',
                'city' => 'Ratlam',
            ],
            [
                'customer_no' => 'CUST-0010',
                'name' => 'Kavita Chawla',
                'mobile' => '9755410101',
                'city' => 'Bhopal',
            ],
            [
                'customer_no' => 'CUST-0011',
                'name' => 'Manish Tiwari',
                'mobile' => '9425521212',
                'city' => 'Gwalior',
            ],
            [
                'customer_no' => 'CUST-0012',
                'name' => 'Sneha Kulkarni',
                'mobile' => '9827632323',
                'city' => 'Indore',
            ],
            [
                'customer_no' => 'CUST-0013',
                'name' => 'Sanjay Rathore',
                'mobile' => '9893743434',
                'city' => 'Ujjain',
            ],
            [
                'customer_no' => 'CUST-0014',
                'name' => 'Neha Bhatia',
                'mobile' => '9424854545',
                'city' => 'Jabalpur',
            ],
            [
                'customer_no' => 'CUST-0015',
                'name' => 'Rahul Saxena',
                'mobile' => '9826965656',
                'city' => 'Bhopal',
            ],
            [
                'customer_no' => 'CUST-0016',
                'name' => 'Geeta Yadav',
                'mobile' => '9893076767',
                'city' => 'Indore',
            ],
            [
                'customer_no' => 'CUST-0017',
                'name' => 'Amit Singhania',
                'mobile' => '9755187878',
                'city' => 'Sagar',
            ],
            [
                'customer_no' => 'CUST-0018',
                'name' => 'Rohit Chauhan',
                'mobile' => '9425298989',
                'city' => 'Dewas',
            ],
            [
                'customer_no' => 'CUST-0019',
                'name' => 'Meena Rathi',
                'mobile' => '9827309090',
                'city' => 'Ujjain',
            ],
            [
                'customer_no' => 'CUST-0020',
                'name' => 'Vikas Porwal',
                'mobile' => '9893410111',
                'city' => 'Indore',
            ],
        ];

        foreach ($customers as $data) {
            Customer::firstOrCreate(
                ['customer_no' => $data['customer_no']],
                $data
            );
        }
    }
}
