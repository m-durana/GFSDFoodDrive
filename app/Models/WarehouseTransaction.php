<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Models\Scopes\SeasonScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseTransaction extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new SeasonScope);

        static::creating(function (WarehouseTransaction $txn) {
            if (empty($txn->season_year)) {
                $txn->season_year = Setting::get('season_year', date('Y'));
            }
            if (empty($txn->scanned_at)) {
                $txn->scanned_at = now();
            }
        });
    }

    protected $fillable = [
        'season_year', 'item_id', 'category_id', 'family_id', 'child_id',
        'transaction_type', 'quantity', 'source', 'donor_name',
        'barcode_scanned', 'notes', 'scanned_by', 'scanned_at',
        'volunteer_name', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'transaction_type' => TransactionType::class,
            'scanned_at' => 'datetime',
            'quantity' => 'integer',
            'season_year' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(WarehouseCategory::class, 'category_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class, 'item_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
