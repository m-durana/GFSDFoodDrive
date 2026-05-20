<?php

namespace App\Models;

use App\Enums\DeliveryLogStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryLog extends Model
{
    use \App\Traits\Auditable;

    protected $fillable = [
        'family_id',
        'user_id',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => DeliveryLogStatus::class,
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
