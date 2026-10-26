<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BhavBookingPayment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bhav_booking_payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bhav_booking_id',
        'mode',
        'online_method',
        'reference_no',
        'amount',
        'source',
        'sort_order',
        'paid_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount'     => 'decimal:2',
        'sort_order' => 'integer',
        'paid_at'    => 'datetime',
    ];

    /**
     * Each payment belongs to a parent BhavBooking.
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(BhavBooking::class, 'bhav_booking_id');
    }
}
