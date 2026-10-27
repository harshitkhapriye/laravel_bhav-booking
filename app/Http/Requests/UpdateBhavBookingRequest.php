<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class UpdateBhavBookingRequest extends StoreBhavBookingRequest
{
    /**
     * Get the validation rules that apply to the update request.
     * Why: Extends StoreBhavBookingRequest so that all validation rules for customer,
     * items, weight, rate, narration, and whatsapp are identical to store().
     * We remove the request_token rule because existing bookings already have
     * their permanent request_token assigned at creation time.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        // Remove request_token and customer_id validation on updates:
        // Existing bookings retain their permanent request_token, and customer cannot be changed in edit mode
        unset($rules['request_token'], $rules['customer_id']);

        // Metal is required for single booking update
        $rules['metal'] = ['required', 'in:GOLD,SILVER'];

        return $rules;
    }

    /**
     * Configure the validator instance to reject rows of a different metal.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $metal = $this->input('metal');
            $items = $this->input('items', []);
            if ($metal && is_array($items)) {
                foreach ($items as $index => $item) {
                    if (isset($item['metal']) && $item['metal'] !== $metal) {
                        $validator->errors()->add("items.{$index}.metal", "All items must be of {$metal} metal for this booking.");
                    }
                }
            }
        });
    }
}
