<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFamilyDeliveryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'delivery_status' => ['required', 'string', 'in:pending,in_transit,delivered'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
