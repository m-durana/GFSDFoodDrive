<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGiftBankItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is permission:santa gated.
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:500'],
            'age_range' => ['nullable', 'string', 'max:50'],
            'gender_suitability' => ['nullable', 'string', 'in:male,female,neutral'],
            'gift_type' => ['nullable', 'string', 'max:100'],
            'donor_name' => ['nullable', 'string', 'max:200'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
