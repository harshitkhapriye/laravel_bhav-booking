<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BhavBookingItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bhav_booking_items';

    /**
     * The attributes that are mass assignable.
     * Why: Allows mass-assignment when inserting multi-weight entry lines.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bhav_booking_id',
        'sort_order',
        'weight',
        'rate',
        'amount',
    ];

    /**
     * The attributes that should be cast to native types.
     * Why: Ensures numbers retain correct decimal precision in JSON / PHP.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'weight' => 'decimal:3',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * Each item line belongs to a parent BhavBooking.
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(BhavBooking::class, 'bhav_booking_id');
    }
}
