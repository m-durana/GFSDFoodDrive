<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingCheck extends Model
{
    protected $fillable = [
        'shopping_assignment_id',
        'item_key',
        'checked_by',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ShoppingAssignment::class, 'shopping_assignment_id');
    }
}
