<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ListBhavBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * All users on the terminal can view bhav bookings.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Sanitizes inputs and normalizes casing.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('trade_type') && is_string($this->trade_type)) {
            $trimmed = trim($this->trade_type);
            if ($trimmed !== '') {
                $merge['trade_type'] = strtoupper($trimmed);
            }
        }

        if ($this->has('metal') && is_string($this->metal)) {
            $trimmed = trim($this->metal);
            if ($trimmed !== '') {
                $merge['metal'] = strtoupper($trimmed);
            }
        }

        if ($this->has('entry_type') && is_string($this->entry_type)) {
            $trimmed = trim($this->entry_type);
            if ($trimmed !== '') {
                $merge['entry_type'] = strtoupper($trimmed);
            }
        }

        if ($this->has('view') && is_string($this->view)) {
            $trimmed = trim($this->view);
            if ($trimmed !== '') {
                $merge['view'] = strtolower($trimmed);
            }
        }

        if ($this->has('per_page') && is_numeric($this->per_page)) {
            $merge['per_page'] = (int) $this->per_page;
        }

        if ($this->has('page') && is_numeric($this->page)) {
            $merge['page'] = (int) $this->page;
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'party' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => [
                'nullable',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) {
                    if ($this->filled('date_from') && $this->filled('date_to')) {
                        if ($value < $this->input('date_from')) {
                            $fail('The date to must be a date after or equal to date from.');
                        }
                    }
                },
            ],
            'trade_type' => ['nullable', 'string', 'in:ALL,BUY,SELL'],
            'metal' => ['nullable', 'string', 'in:ALL,GOLD,SILVER'],
            'entry_type' => ['nullable', 'string', 'in:ALL,ONLY_ENTRY,BHAV_CUT_LEDGER'],
            'view' => ['nullable', 'string', 'in:current,old'],
            'per_page' => ['nullable', 'integer', 'in:10,50,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     * Always return a JSON response even when accessed via browser address bar.
     *
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
