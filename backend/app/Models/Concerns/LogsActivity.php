<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Records create/update/delete events performed by authenticated staff.
 *
 * @mixin Model
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn (Model $model) => ActivityLog::record('created', $model));
        static::updated(function (Model $model): void {
            $changes = array_diff(array_keys($model->getChanges()), ['updated_at', 'updated_by']);

            if ($changes !== []) {
                ActivityLog::record('updated', $model, ['fields' => array_values($changes)]);
            }
        });
        static::deleted(fn (Model $model) => ActivityLog::record('deleted', $model));
    }

    public function activityLabel(): string
    {
        return (string) ($this->getAttribute('title') ?? $this->getAttribute('name') ?? $this->getAttribute('label') ?? '#'.$this->getKey());
    }
}
