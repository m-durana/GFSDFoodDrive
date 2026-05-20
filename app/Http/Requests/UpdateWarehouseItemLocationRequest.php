<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseItemLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is permission-middleware gated upstream (santa/coordinator).
        // Don't re-restrict here — the original inline validate() had no
        // additional auth check beyond the route middleware.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'location_zone' => 'nullable|string|max:10',
            'location_shelf' => 'nullable|string|max:10',
            'location_bin' => 'nullable|string|max:20',
        ];
    }
}
