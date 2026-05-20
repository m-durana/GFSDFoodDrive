<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function created(Model $model): void
    {
        $this->log($model, 'created', null, $this->snapshot($model));
    }

    public function updated(Model $model): void
    {
        $dirty = $model->getChanges();
        $excluded = method_exists($model, 'getAuditExcludedAttributes')
            ? $model->getAuditExcludedAttributes()
            : [];

        $tracked = array_diff_key($dirty, array_flip($excluded));
        if (empty($tracked)) {
            return;
        }

        $before = [];
        foreach (array_keys($tracked) as $key) {
            $before[$key] = $model->getOriginal($key);
        }

        $this->log($model, 'updated', $before, $tracked);
    }

    public function deleted(Model $model): void
    {
        // SoftDeletes will fire deleted both for soft and force. We log either.
        $action = method_exists($model, 'trashed') && $model->trashed() ? 'deleted' : 'deleted';
        $this->log($model, $action, $this->snapshot($model), null);
    }

    public function restored(Model $model): void
    {
        $this->log($model, 'restored', null, $this->snapshot($model));
    }

    private function snapshot(Model $model): array
    {
        $excluded = method_exists($model, 'getAuditExcludedAttributes')
            ? $model->getAuditExcludedAttributes()
            : [];

        return array_diff_key($model->getAttributes(), array_flip($excluded));
    }

    private function log(Model $model, string $action, ?array $before, ?array $after): void
    {
        AuditLog::create([
            'auditable_type' => $model::class,
            'auditable_id'   => $model->getKey(),
            'actor_id'       => auth()->id(),
            'action'         => $action,
            'before'         => $before,
            'after'          => $after,
            'ip'             => request()?->ip(),
            'created_at'     => now(),
        ]);
    }
}
