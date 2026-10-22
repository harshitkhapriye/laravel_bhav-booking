<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_no',
        'name',
        'mobile',
        'city',
    ];

    /**
     * Generate the next sequential customer number (e.g. CUST-0001).
     *
     * @return string
     */
    public static function generateNextCustomerNo(): string
    {
        $lastCustomer = static::orderBy('id', 'desc')->first();

        if ($lastCustomer && preg_match('/CUST-(\d+)/i', $lastCustomer->customer_no, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = static::count() + 1;
        }

        // Guarantee uniqueness in case of deleted records or manual numbers
        do {
            $customerNo = sprintf('CUST-%04d', $nextNumber);
            $exists = static::where('customer_no', $customerNo)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $customerNo;
    }

    /**
     * Alias for generateNextCustomerNo for convenience.
     *
     * @return string
     */
    public static function nextCustomerNo(): string
    {
        return static::generateNextCustomerNo();
    }
}
