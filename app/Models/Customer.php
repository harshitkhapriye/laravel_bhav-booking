<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'party_type',
        'parent_customer_id',
        'mobile',
        'city',
        'email',
        'address',
        'profile_image',
        'birthday',
        'anniversary',
        'aadhaar_no',
        'pan_no',
        'gst_no',
        'lic_no',
        'reg_no',
        'logo_title',
        'shop_name',
        'state',
        'district',
        'area',
        'shop_address',
        'shop_logo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'aadhaar_no',
        'pan_no',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birthday' => 'date',
        'anniversary' => 'date',
        'aadhaar_no' => 'encrypted',
        'pan_no' => 'encrypted',
    ];

    /**
     * The accessors to append to the model's array and JSON form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_image_url',
        'shop_logo_url',
    ];

    /**
     * Get the public URL for the profile image.
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        if (! empty($this->profile_image)) {
            return asset('storage/'.$this->profile_image);
        }

        return null;
    }

    /**
     * Get the public URL for the shop logo.
     */
    public function getShopLogoUrlAttribute(): ?string
    {
        if (! empty($this->shop_logo)) {
            return asset('storage/'.$this->shop_logo);
        }

        return null;
    }

    /**
     * Parent customer relationship for family sub-profiles.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'parent_customer_id');
    }

    /**
     * Family members relationship for parent profiles.
     */
    public function familyMembers(): HasMany
    {
        return $this->hasMany(Customer::class, 'parent_customer_id');
    }

    /**
     * Bhav bookings relationship.
     */
    public function bhavBookings(): HasMany
    {
        return $this->hasMany(BhavBooking::class, 'customer_id');
    }

    /**
     * Generate the next sequential customer number (e.g. CUST-0001).
     */
    public static function generateNextCustomerNo(bool $lock = false): string
    {
        $query = static::orderBy('id', 'desc');
        if ($lock) {
            $query->lockForUpdate();
        }
        $lastCustomer = $query->first();

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
     */
    public static function nextCustomerNo(): string
    {
        return static::generateNextCustomerNo();
    }
}
