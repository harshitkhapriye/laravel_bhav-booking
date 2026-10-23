<?php

namespace App\Http\Requests;

class UpdateBhavBookingRequest extends StoreBhavBookingRequest
{
    /**
     * Get the validation rules that apply to the update request.
     * Why: Extends StoreBhavBookingRequest so that all validation rules for customer,
     * items, weight, rate, narration, and whatsapp are identical to store().
     * We remove the request_token rule because existing bookings already have
     * their permanent request_token assigned at creation time.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        // Remove request_token and customer_id validation on updates:
        // Existing bookings retain their permanent request_token, and customer cannot be changed in edit mode
        unset($rules['request_token'], $rules['customer_id']);

        return $rules;
    }
}
