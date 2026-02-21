<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'family_number',
        'family_name',
        'address',
        'phone1',
        'phone2',
        'email',
        'female_adults',
        'male_adults',
        'number_of_adults',
        'infants',
        'young_children',
        'children_count',
        'tweens',
        'teenagers',
        'number_of_children',
        'number_of_family_members',
        'has_crhs_children',
        'has_gfhs_children',
        'pet_information',
        'delivery_preference',
        'delivery_date',
        'delivery_time',
        'need_for_help',
        'severe_need',
        'other_questions',
    ];

    protected function casts(): array
    {
        return [
            'has_crhs_children' => 'boolean',
            'has_gfhs_children' => 'boolean',
            'family_number' => 'integer',
            'female_adults' => 'integer',
            'male_adults' => 'integer',
            'number_of_adults' => 'integer',
            'infants' => 'integer',
            'young_children' => 'integer',
            'children_count' => 'integer',
            'tweens' => 'integer',
            'teenagers' => 'integer',
            'number_of_children' => 'integer',
            'number_of_family_members' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }
}
