<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBhavBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Why: We return true because any authenticated or guest user on this terminal can save bookings.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxWeight = config('bhav.max_weight', 99999.999);
        $maxRate = config('bhav.max_rate', 9999999.99);

        return [
            'customer_id'   => ['required', 'integer', 'exists:customers,id'],
            'trade_type'    => ['required', 'in:BUY,SELL'],
            'entry_type'    => ['required', 'in:ONLY_ENTRY,BHAV_CUT_LEDGER'],
            'metal'         => ['required', 'in:GOLD,SILVER'],
            'items'         => ['required', 'array', 'min:1', 'max:50'],
            'items.*.weight'=> ['required', 'numeric', 'gt:0', "max:{$maxWeight}"],
            'items.*.rate'  => ['required', 'numeric', 'gt:0', "max:{$maxRate}"],
            'narration'     => ['nullable', 'string', 'max:255'],
            'send_whatsapp' => ['boolean'],
            'request_token' => ['required', 'uuid'],
        ];
    }

    /**
     * Custom friendly attribute names for error messages.
     * Why: Shows e.g. "Item 1 weight must be greater than 0" instead of cryptic field names.
     */
    public function attributes(): array
    {
        return [
            'customer_id'    => 'customer',
            'trade_type'     => 'trade type',
            'entry_type'     => 'entry type',
            'metal'          => 'metal',
            'items'          => 'booking items',
            'items.*.weight' => 'weight',
            'items.*.rate'   => 'rate',
            'request_token'  => 'request token',
        ];
    }
}
