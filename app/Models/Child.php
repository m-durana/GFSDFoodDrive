<?php

namespace App\Models;

use App\Enums\GiftLevel;
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
        'toy_ideas',
        'all_sizes',
        'mail_merged',
        'gifts_received',
        'gift_level',
        'where_is_tag',
        'adopter_name',
        'adopter_contact_info',
    ];

    protected function casts(): array
    {
        return [
            'mail_merged' => 'boolean',
            'gift_level' => GiftLevel::class,
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
