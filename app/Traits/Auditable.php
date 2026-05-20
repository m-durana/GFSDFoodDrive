<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Observers\AuditableObserver;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Apply to any Eloquent model to record created/updated/deleted/restored
 * changes into the `audit_logs` table via AuditableObserver. Per-model
 * suppression list is the `$auditExclude` property on the model (defaults
 * to an empty list — passwords/tokens/timestamps still filter below).
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::observe(AuditableObserver::class);
    }

    public function audits(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Attributes that should never appear in audit diffs (low-value churn or
     * secrets). Models may extend by declaring $auditExclude.
     */
    public function getAuditExcludedAttributes(): array
    {
        $defaults = [
            'updated_at',
            'created_at',
            'deleted_at',
            'remember_token',
            'password',
            'driver_pin_hash',
            'driver_pin_encrypted',
            'status_token',
            'access_token',
            'adoption_token',
            'route_geometry',
            'geometry_updated_at',
        ];
        $local = property_exists($this, 'auditExclude') ? (array) $this->auditExclude : [];
        return array_values(array_unique(array_merge($defaults, $local)));
    }
}
