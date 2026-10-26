<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('pan_no') && is_string($this->input('pan_no'))) {
            $pan = strtoupper(trim($this->input('pan_no')));
            $this->merge(['pan_no' => $pan !== '' ? $pan : null]);
        }

        if ($this->has('gst_no') && is_string($this->input('gst_no'))) {
            $gst = strtoupper(trim($this->input('gst_no')));
            $this->merge(['gst_no' => $gst !== '' ? $gst : null]);
        }

        $isFamilySubProfile = filter_var($this->input('is_family_sub_profile'), FILTER_VALIDATE_BOOLEAN);
        $this->merge(['is_family_sub_profile' => $isFamilySubProfile]);

        if (!$this->has('party_type') || empty($this->input('party_type'))) {
            $this->merge(['party_type' => 'CUSTOMER']);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:100'],
            'mobile'                => $this->getMobileRules(),
            'party_type'            => ['required', 'in:CUSTOMER,SUPPLIER'],
            'is_family_sub_profile' => ['boolean'],
            'parent_customer_id'    => ['required_if:is_family_sub_profile,true', 'nullable', 'exists:customers,id'],
            'email'                 => ['nullable', 'email', 'max:150'],
            'address'               => ['nullable', 'string', 'max:500'],
            'birthday'              => ['nullable', 'date_format:Y-m-d', 'after_or_equal:1900-01-01', 'before_or_equal:today'],
            'anniversary'           => ['nullable', 'date_format:Y-m-d', 'after_or_equal:1900-01-01', 'before_or_equal:today'],
            'aadhaar_no'            => ['nullable', 'digits:12'],
            'pan_no'                => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'gst_no'                => ['nullable', 'regex:/^[0-9]{2}[A-Z0-9]{13}$/'],
            'lic_no'                => ['nullable', 'string', 'max:100'],
            'reg_no'                => ['nullable', 'string', 'max:100'],
            'logo_title'            => ['nullable', 'string', 'max:100'],
            'shop_name'             => ['nullable', 'string', 'max:100'],
            'area'                  => ['nullable', 'string', 'max:100'],
            'state'                 => ['nullable', 'string', 'max:60'],
            'district'              => ['nullable', 'string', 'max:60'],
            'shop_address'          => ['nullable', 'string', 'max:500'],
            'profile_image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'shop_logo'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'city'                  => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Build mobile rules including unique exception for family sub-profiles.
     */
    protected function getMobileRules(?int $ignoreCustomerId = null): array
    {
        $rules = ['required', 'digits:10'];

        $isFamily = filter_var($this->input('is_family_sub_profile'), FILTER_VALIDATE_BOOLEAN);
        $parentId = $this->input('parent_customer_id');

        $allowSameMobileAsParent = false;
        if ($isFamily && !empty($parentId)) {
            $parent = Customer::find($parentId);
            if ($parent && (string) $parent->mobile === (string) $this->input('mobile')) {
                $allowSameMobileAsParent = true;
            }
        }

        if (!$allowSameMobileAsParent) {
            $uniqueRule = Rule::unique('customers', 'mobile');
            if ($ignoreCustomerId) {
                $uniqueRule->ignore($ignoreCustomerId);
            }
            $rules[] = $uniqueRule;
        }

        return $rules;
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'                  => 'Party name is required',
            'name.max'                       => 'Party name may not be greater than 100 characters',
            'mobile.required'                => 'Mobile number is required',
            'mobile.digits'                  => 'Mobile number must be 10 digits',
            'mobile.unique'                  => 'This mobile number is already registered',
            'party_type.required'            => 'Party type is required',
            'party_type.in'                  => 'Party type must be Customer or Supplier',
            'parent_customer_id.required_if' => 'Please select the main customer for this family sub-profile',
            'parent_customer_id.exists'      => 'Selected main customer was not found',
            'email.email'                    => 'Please enter a valid email address',
            'email.max'                      => 'Email may not be greater than 150 characters',
            'address.max'                    => 'Party address may not be greater than 500 characters',
            'birthday.date_format'           => 'Birthday must be in YYYY-MM-DD format',
            'birthday.after_or_equal'        => 'Birthday year must be 1900 or later',
            'birthday.before_or_equal'       => 'Birthday cannot be a future date',
            'anniversary.date_format'        => 'Anniversary must be in YYYY-MM-DD format',
            'anniversary.after_or_equal'     => 'Anniversary year must be 1900 or later',
            'anniversary.before_or_equal'    => 'Anniversary cannot be a future date',
            'aadhaar_no.digits'              => 'Aadhaar number must be 12 digits',
            'pan_no.regex'                   => 'Invalid PAN format (e.g. ABCDE1234F)',
            'gst_no.regex'                   => 'Invalid GST number format (15 characters, e.g. 22AAAAA0000A1Z5)',
            'lic_no.max'                     => 'License number may not be greater than 100 characters',
            'reg_no.max'                     => 'Registration number may not be greater than 100 characters',
            'logo_title.max'                 => 'Logo title may not be greater than 100 characters',
            'shop_name.max'                  => 'Shop / Business name may not be greater than 100 characters',
            'area.max'                       => 'Area / Landmark may not be greater than 100 characters',
            'state.max'                      => 'State may not be greater than 60 characters',
            'district.max'                   => 'District may not be greater than 60 characters',
            'shop_address.max'               => 'Shop address may not be greater than 500 characters',
            'profile_image.image'            => 'Profile image must be an image',
            'profile_image.mimes'            => 'Profile image must be a JPG, JPEG, PNG, or WEBP file',
            'profile_image.max'              => 'Profile image may not be larger than 2MB',
            'shop_logo.image'                => 'Shop logo must be an image',
            'shop_logo.mimes'                => 'Shop logo must be a JPG, JPEG, PNG, or WEBP file',
            'shop_logo.max'                  => 'Shop logo may not be larger than 2MB',
        ];
    }
}
