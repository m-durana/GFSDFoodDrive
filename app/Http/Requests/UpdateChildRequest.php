<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChildRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Per-family access + child binding is checked in the controller.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'gender' => ['required', 'string', 'in:Male,Female,Other'],
            'age' => ['required', 'string', 'max:50'],
            'school' => ['nullable', 'string', 'max:255'],
            'clothes_size' => ['nullable', 'string', 'max:255'],
            'clothing_styles' => ['nullable', 'string', 'max:1000'],
            'clothing_options' => ['nullable', 'string', 'max:1000'],
            'gift_preferences' => ['nullable', 'string', 'max:1000'],
            'toy_ideas' => ['nullable', 'string', 'max:1000'],
            'all_sizes' => ['nullable', 'string', 'max:1000'],
            'gifts_received' => ['nullable', 'string', 'max:1000'],
            'gift_level' => ['nullable', 'integer', 'min:0', 'max:3'],
            'where_is_tag' => ['nullable', 'string', 'max:255'],
            'adopter_name' => ['nullable', 'string', 'max:255'],
            'adopter_email' => ['nullable', 'email', 'max:255'],
            'adopter_phone' => ['nullable', 'string', 'max:255'],
        ];
    }
}
