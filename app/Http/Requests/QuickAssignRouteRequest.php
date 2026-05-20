<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuickAssignRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'driver_name' => ['required', 'string', 'max:255'],
            'driver_user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn($q) => $q->where('permission', '>=', 8)),
            ],
            'batch_size' => ['nullable', 'integer', 'min:1', 'max:20'],
            'start_lat' => ['nullable', 'numeric'],
            'start_lng' => ['nullable', 'numeric'],
        ];
    }
}
