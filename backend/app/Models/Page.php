<?php

namespace App\Models;

use App\Enums\PageStatus;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\RevalidatesFrontend;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, LogsActivity, RevalidatesFrontend, SoftDeletes;

    public const HOME_SLUG = 'home';

    protected $fillable = [
        'title', 'slug', 'eyebrow', 'summary', 'blocks', 'status', 'is_system', 'published_at',
        'seo_title', 'seo_description', 'og_image_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'blocks' => 'array',
            'is_system' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Page $page): void {
            if ($page->status === PageStatus::Published && $page->published_at === null) {
                $page->published_at = now();
            }

            if (Auth::check()) {
                $page->updated_by = Auth::id();
            }
        });
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PageStatus::Published);
    }

    public function revalidationTags(): array
    {
        return ['pages', 'page:'.$this->slug];
    }
}
