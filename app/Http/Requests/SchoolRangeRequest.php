<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // santa.* routes already gate via the permission:santa middleware.
        // Allow here so this FormRequest can be reused if reused under that gate.
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'school_name' => ['required', 'string', 'max:255'],
            'range_start' => ['required', 'integer', 'min:0'],
            'range_end'   => ['required', 'integer', 'min:0', 'gt:range_start'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'range_end.gt' => 'The end of the range must be greater than the start.',
        ];
    }
}
