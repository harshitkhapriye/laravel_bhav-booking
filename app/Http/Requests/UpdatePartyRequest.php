<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdatePartyRequest extends StorePartyRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        // Retrieve the customer from route parameter ({customer})
        $customer = $this->route('customer');
        $customerId = $customer instanceof Customer ? $customer->id : (int) $customer;

        // Mobile unique rule ignoring current party
        $rules['mobile'] = $this->getMobileRules($customerId);

        // Disallow setting self as parent
        if ($customerId) {
            $rules['parent_customer_id'][] = Rule::notIn([$customerId]);
        }

        // Aadhaar: if typed, validate 12 digits; if left empty, keep existing value
        $rules['aadhaar_no'] = ['nullable', 'digits:12'];

        // PAN: if typed, validate format; if left empty, keep existing value
        $rules['pan_no'] = ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'];

        // Optional image removal flags
        $rules['remove_profile_image'] = ['nullable', 'boolean'];
        $rules['remove_shop_logo'] = ['nullable', 'boolean'];

        return $rules;
    }
}
