<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BhavBooking extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_no',
        'customer_id',
        'trade_type',
        'entry_type',
        'metal',
        'total_weight',
        'total_amount',
        'delivered_weight',
        'given_amount',
        'received_amount',
        'status',
        'narration',
        'send_whatsapp',
        'request_token',
        'booked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_weight' => 'decimal:3',
        'total_amount' => 'decimal:2',
        'delivered_weight' => 'decimal:3',
        'given_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'send_whatsapp' => 'boolean',
        'booked_at' => 'datetime',
    ];

    /**
     * Accessors to append to the model's array/JSON form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'pending_weight',
    ];

    /**
     * A Bhav Booking has many line items ordered by sort_order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BhavBookingItem::class, 'bhav_booking_id')->orderBy('sort_order', 'asc');
    }

    /**
     * A Bhav Booking has many payments ordered by sort_order.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BhavBookingPayment::class, 'bhav_booking_id')->orderBy('sort_order', 'asc');
    }

    /**
     * A Bhav Booking has many activity logs ordered newest first.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(BhavBookingLog::class, 'bhav_booking_id')->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    /**
     * A Bhav Booking belongs to a Customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Accessor: pending_weight = total_weight - delivered_weight
     */
    public function getPendingWeightAttribute(): float
    {
        $total = (float) ($this->total_weight ?? 0);
        $delivered = (float) ($this->delivered_weight ?? 0);

        return round($total - $delivered, 3);
    }

    /**
     * Generate the next unique booking number formatted like "BB-000001".
     * Supports optional row locking inside database transactions.
     */
    public static function nextBookingNo(bool $lock = false): string
    {
        $query = static::withTrashed()->orderBy('id', 'desc');

        if ($lock) {
            $query->lockForUpdate();
        }

        $lastBooking = $query->first();

        if ($lastBooking && preg_match('/BB-(\d+)/i', $lastBooking->booking_no, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = static::withTrashed()->count() + 1;
        }

        // Guarantee uniqueness in case of race conditions or gaps
        do {
            $bookingNo = sprintf('BB-%06d', $nextNumber);
            $exists = static::withTrashed()->where('booking_no', $bookingNo)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $bookingNo;
    }
}
