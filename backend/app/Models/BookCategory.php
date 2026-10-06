<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\RevalidatesFrontend;
use Database\Factories\BookCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BookCategory extends Model
{
    /** @use HasFactory<BookCategoryFactory> */
    use HasFactory, LogsActivity, RevalidatesFrontend;

    protected $fillable = [
        'name', 'slug', 'headline', 'description', 'image_id', 'sort_order', 'is_published', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
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
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function revalidationTags(): array
    {
        return ['books', 'book-categories'];
    }
}
