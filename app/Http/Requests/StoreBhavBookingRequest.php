<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * Prepare the data for validation.
     * Backwards compatibility: if top-level metal is provided and items.*.metal is missing, default item metal to top-level metal.
     */
    protected function prepareForValidation(): void
    {
        $items = $this->input('items');
        $metal = $this->input('metal');

        if (is_array($items) && $metal) {
            foreach ($items as &$item) {
                if (is_array($item) && ! isset($item['metal'])) {
                    $item['metal'] = $metal;
                }
            }
            $this->merge(['items' => $items]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxWeight = config('bhav.max_weight', 99999.999);
        $maxRate = config('bhav.max_rate', 9999999.99);

        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'trade_type' => ['required', 'in:BUY,SELL'],
            'entry_type' => ['required', 'in:ONLY_ENTRY,BHAV_CUT_LEDGER'],
            'metal' => ['nullable', 'in:GOLD,SILVER'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.metal' => ['required', 'in:GOLD,SILVER'],
            'items.*.weight' => ['required', 'numeric', 'gt:0', "max:{$maxWeight}"],
            'items.*.rate' => ['required', 'numeric', 'gt:0', "max:{$maxRate}"],
            'payments' => ['nullable', 'array', 'max:20'],
            'payments.*.mode' => ['required', 'in:CASH,ONLINE'],
            'payments.*.online_method' => ['nullable', 'required_if:payments.*.mode,ONLINE', 'in:UPI,BANK,CARD,CHEQUE'],
            'payments.*.reference_no' => ['nullable', 'string', 'max:50'],
            'payments.*.amount' => ['required', 'numeric', 'gt:0', 'max:9999999.99'],
            'narration' => ['nullable', 'string', 'max:255'],
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
            'customer_id' => 'customer',
            'trade_type' => 'trade type',
            'entry_type' => 'entry type',
            'metal' => 'metal',
            'items' => 'booking items',
            'items.*.weight' => 'weight',
            'items.*.rate' => 'rate',
            'payments' => 'payments',
            'payments.*.mode' => 'payment mode',
            'payments.*.online_method' => 'online payment method',
            'payments.*.reference_no' => 'reference number',
            'payments.*.amount' => 'payment amount',
            'request_token' => 'request token',
        ];
    }
}
