<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'show_on_website' => ['boolean'],
            'avatar_action' => ['nullable', 'string', Rule::in(['upload', 'randomize', 'remove'])],
            'avatar' => [
                'nullable',
                'required_if:avatar_action,upload',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:max_width=2048,max_height=2048',
            ],
            'avatar_seed' => ['nullable', 'string', 'alpha_dash', 'max:32'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.mimes' => 'Avatar must be a JPG, PNG, or WEBP image (SVG is not allowed).',
            'avatar.dimensions' => 'Avatar dimensions must be 2048x2048 pixels or smaller.',
            'avatar.max' => 'Avatar must be 2 MB or smaller.',
            'avatar.required_if' => 'Please choose an image file to upload.',
            'avatar_seed.alpha_dash' => 'Avatar seed may only contain letters, numbers, dashes, and underscores.',
            'avatar_seed.max' => 'Avatar seed must be 32 characters or fewer.',
        ];
    }
}
