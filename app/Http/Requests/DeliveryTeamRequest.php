<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is permission:santa gated.
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7'],
            'driver_user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn($q) => $q->where('permission', '>=', 8)),
            ],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
