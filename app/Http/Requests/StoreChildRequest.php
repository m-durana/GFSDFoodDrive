<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChildRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Per-family access is checked separately in the controller
        // (authorizeFamilyAccess); FormRequest just covers field shape.
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
        ];
    }
}
