<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Records who created/updated/deleted a row and what changed. Add
 * `use Auditable;` to any model — no migration needed on the host model,
 * the `audit_logs` table already carries the polymorphic `auditable_*` keys.
 * Currently wired to `Employee` only; safe to reuse on other master-data
 * models later.
 */
trait Auditable
{
    protected array $auditableOriginal = [];

    protected static function bootAuditable(): void
    {
        static::created(fn ($model) => $model->recordAudit('created', [], $model->getAttributes()));

        static::updating(function ($model) {
            $model->auditableOriginal = $model->getOriginal();
        });

        static::updated(function ($model) {
            $changes = collect($model->getChanges())
                ->except([$model->getUpdatedAtColumn()])
                ->all();

            if (empty($changes)) {
                return;
            }

            $old = collect($changes)->keys()
                ->mapWithKeys(fn ($key) => [$key => $model->auditableOriginal[$key] ?? null])
                ->all();

            $model->recordAudit('updated', $old, $changes);
        });

        static::deleted(fn ($model) => $model->recordAudit('deleted', $model->getAttributes(), []));
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    protected function recordAudit(string $action, array $old, array $new): void
    {
        $this->auditLogs()->create([
            'action' => $action,
            'user_id' => auth()->id(),
            'changes' => ['old' => $old, 'new' => $new],
        ]);
    }
}
