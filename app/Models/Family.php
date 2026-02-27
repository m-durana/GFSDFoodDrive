<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Models\Scopes\SeasonScope;
use App\Observers\FamilyObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[ObservedBy(FamilyObserver::class)]
class Family extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new SeasonScope);

        static::creating(function (Family $family) {
            if (empty($family->status_token)) {
                $family->status_token = Str::random(32);
            }
            if (empty($family->season_year)) {
                $family->season_year = Setting::get('season_year', date('Y'));
            }
        });
    }

    protected $fillable = [
        'user_id',
        'volunteer_id',
        'season_year',
        'family_number',
        'family_name',
        'address',
        'phone1',
        'phone2',
        'email',
        'preferred_language',
        'female_adults',
        'male_adults',
        'other_adults',
        'number_of_adults',
        'infants',
        'young_children',
        'children_count',
        'tweens',
        'teenagers',
        'number_of_children',
        'number_of_family_members',
        'number_of_boxes',
        'has_crhs_children',
        'has_gfhs_children',
        'needs_baby_supplies',
        'pet_information',
        'delivery_preference',
        'delivery_date',
        'delivery_time',
        'delivery_reason',
        'delivery_team',
        'delivery_team_id',
        'latitude',
        'longitude',
        'delivery_status',
        'need_for_help',
        'severe_need',
        'other_questions',
        'family_done',
        'status_token',
        'delivery_route_id',
        'route_order',
    ];

    protected function casts(): array
    {
        return [
            'has_crhs_children' => 'boolean',
            'has_gfhs_children' => 'boolean',
            'needs_baby_supplies' => 'boolean',
            'family_done' => 'boolean',
            'family_number' => 'integer',
            'female_adults' => 'integer',
            'male_adults' => 'integer',
            'other_adults' => 'integer',
            'number_of_adults' => 'integer',
            'infants' => 'integer',
            'young_children' => 'integer',
            'children_count' => 'integer',
            'tweens' => 'integer',
            'teenagers' => 'integer',
            'number_of_children' => 'integer',
            'number_of_family_members' => 'integer',
            'number_of_boxes' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'delivery_date' => 'date',
            'delivery_status' => DeliveryStatus::class,
            'route_order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    public function deliveryLogs(): HasMany
    {
        return $this->hasMany(DeliveryLog::class);
    }

    public function deliveryRoute(): BelongsTo
    {
        return $this->belongsTo(DeliveryRoute::class);
    }

    public function deliveryTeam(): BelongsTo
    {
        return $this->belongsTo(DeliveryTeam::class);
    }

    // Query Scopes

    public function scopeUnassigned($query)
    {
        return $query->whereNull('family_number');
    }

    public function scopeAssigned($query)
    {
        return $query->whereNotNull('family_number');
    }

    public function scopeSevereNeed($query)
    {
        return $query->where('severe_need', true)->orWhere('severe_need', 'Yes');
    }

    public function scopeNeedsBabySupplies($query)
    {
        return $query->where('needs_baby_supplies', true);
    }

    public function scopeNeedsDelivery($query)
    {
        return $query->where('delivery_preference', 'Delivery');
    }

    public function scopePickup($query)
    {
        return $query->where('delivery_preference', 'Pickup');
    }

    public function scopeDone($query)
    {
        return $query->where('family_done', true);
    }

    public function scopeNotDone($query)
    {
        return $query->where(function ($q) {
            $q->where('family_done', false)->orWhereNull('family_done');
        });
    }

    public function scopeGeocodeable($query)
    {
        return $query->whereNull('latitude')
            ->whereNotNull('address')
            ->where('address', '!=', '');
    }
}
