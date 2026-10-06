<?php

namespace App\Models\Concerns;

use App\Jobs\RevalidateFrontend;
use Illuminate\Database\Eloquent\Model;

/**
 * Notifies the Next.js frontend to purge cached data after content changes.
 *
 * @mixin Model
 */
trait RevalidatesFrontend
{
    public static function bootRevalidatesFrontend(): void
    {
        $dispatch = function (Model $model): void {
            RevalidateFrontend::dispatch($model->revalidationTags())->afterCommit();
        };

        static::saved($dispatch);
        static::deleted($dispatch);

        if (method_exists(static::class, 'restored')) {
            static::restored($dispatch);
        }
    }

    /**
     * @return list<string>
     */
    abstract public function revalidationTags(): array;
}
