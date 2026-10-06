<?php

namespace App\Models;

use App\Enums\ServiceGroup;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\RevalidatesFrontend;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, LogsActivity, RevalidatesFrontend;

    protected $fillable = [
        'title', 'slug', 'group', 'summary', 'body', 'highlights', 'image_id', 'link_label', 'link_url', 'sort_order', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'group' => ServiceGroup::class,
            'highlights' => 'array',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function revalidationTags(): array
    {
        return ['services', 'pages'];
    }
}
