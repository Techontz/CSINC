<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'event', 'subject_type', 'subject_id', 'description', 'properties'];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public static function record(string $event, ?Model $subject, array $properties = [], ?string $description = null): ?self
    {
        $userId = Auth::id();

        if ($userId === null && $description === null) {
            return null;
        }

        $label = $subject && method_exists($subject, 'activityLabel') ? $subject->activityLabel() : null;
        $type = $subject ? str(class_basename($subject))->snake(' ')->toString() : null;

        return static::query()->create([
            'user_id' => $userId,
            'event' => $event,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description ?? trim("{$event} {$type} “{$label}”"),
            'properties' => $properties ?: null,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
