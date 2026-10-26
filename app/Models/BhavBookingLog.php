<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BhavBookingLog extends Model
{
    use HasFactory;

    /**
     * Disable standard updated_at since this is an append-only log table.
     */
    public $timestamps = false;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bhav_booking_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * Attribute casts.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Accessors to append to array/JSON representation.
     */
    protected $appends = [
        'summary',
    ];

    /**
     * Booking relationship.
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(BhavBooking::class, 'bhav_booking_id');
    }

    /**
     * Generate a concise human-readable summary of the log entry.
     *
     * @return string
     */
    public function getSummaryAttribute(): string
    {
        switch ($this->action) {
            case 'CREATED':
                $weight = $this->new_values['total_weight'] ?? 0;
                $amount = isset($this->new_values['total_amount']) ? number_format((float) $this->new_values['total_amount'], 2) : '0.00';
                return "Created booking (Weight: {$weight} gm, Amount: ₹{$amount})";

            case 'UPDATED':
                $diffs = [];
                $old = $this->old_values ?? [];
                $new = $this->new_values ?? [];

                if (isset($old['total_weight'], $new['total_weight']) && (float) $old['total_weight'] !== (float) $new['total_weight']) {
                    $diffs[] = "Weight {$old['total_weight']} → {$new['total_weight']} gm";
                }
                if (isset($old['total_amount'], $new['total_amount']) && (float) $old['total_amount'] !== (float) $new['total_amount']) {
                    $oldAmt = number_format((float) $old['total_amount'], 2);
                    $newAmt = number_format((float) $new['total_amount'], 2);
                    $diffs[] = "Amount ₹{$oldAmt} → ₹{$newAmt}";
                }
                if (isset($old['trade_type'], $new['trade_type']) && $old['trade_type'] !== $new['trade_type']) {
                    $diffs[] = "Type {$old['trade_type']} → {$new['trade_type']}";
                }
                if (isset($old['metal'], $new['metal']) && $old['metal'] !== $new['metal']) {
                    $diffs[] = "Metal {$old['metal']} → {$new['metal']}";
                }
                if (isset($old['narration'], $new['narration']) && $old['narration'] !== $new['narration']) {
                    $diffs[] = "Narration updated";
                }
                if (isset($old['payments_count'], $new['payments_count']) && $old['payments_count'] !== $new['payments_count']) {
                    $diffs[] = "Payments updated";
                }

                return !empty($diffs) ? implode(', ', $diffs) : 'Updated booking details';

            case 'DELETED':
                return 'Soft deleted booking';

            case 'SILENT_DELETED':
                return 'Silent deleted booking';

            case 'PRINTED':
                return 'Voucher printed';

            case 'SHARED':
                return 'Shared via WhatsApp';

            default:
                return (string) $this->action;
        }
    }
}
