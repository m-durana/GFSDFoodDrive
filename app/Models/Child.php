<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'gender',
        'age',
        'school',
        'clothes_size',
        'clothing_styles',
        'clothing_options',
        'gift_preferences',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
