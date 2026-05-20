<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSanta();
    }

    public function rules(): array
    {
        $positions = array_values(array_filter(array_map(
            'trim',
            explode(',', Setting::get('coordinator_positions', ''))
        )));

        return [
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'password' => ['required', Password::min(8)],
            'role' => ['required', 'string', 'in:family,advisor,coordinator,system_coordinator,santa,ninja'],
            'school_source' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255', Rule::in($positions)],
        ];
    }
}
