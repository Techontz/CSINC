<?php

namespace App\Models;

use App\Enums\NavigationLocation;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\RevalidatesFrontend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationItem extends Model
{
    use LogsActivity, RevalidatesFrontend;

    protected $fillable = ['location', 'parent_id', 'label', 'url', 'description', 'open_in_new_tab', 'is_visible', 'sort_order'];

    protected function casts(): array
    {
        return [
            'location' => NavigationLocation::class,
            'open_in_new_tab' => 'boolean',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function revalidationTags(): array
    {
        return ['site'];
    }
}
