<?php

namespace App\Models;

use App\Enums\BookStatus;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\RevalidatesFrontend;
use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory, LogsActivity, RevalidatesFrontend, SoftDeletes;

    /** Disk holding paid download files; never publicly served. */
    public const PRIVATE_DISK = 'local';

    protected $fillable = [
        'title', 'slug', 'subtitle', 'author', 'co_authors', 'short_description', 'description',
        'sku', 'isbn', 'publisher', 'publication_date', 'edition', 'language', 'page_count', 'format',
        'price_cents', 'sale_price_cents', 'currency', 'external_purchase_url',
        'cover_id', 'og_image_id', 'file_path', 'file_original_name', 'file_size', 'sample_path',
        'status', 'is_featured', 'published_at', 'sort_order', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookStatus::class,
            'co_authors' => 'array',
            'publication_date' => 'date',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'price_cents' => 'integer',
            'sale_price_cents' => 'integer',
            'page_count' => 'integer',
            'sort_order' => 'integer',
            'file_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Book $book): void {
            if ($book->status === BookStatus::Published && $book->published_at === null) {
                $book->published_at = now();
            }

            if ($book->isDirty('file_path') && $book->file_path) {
                $disk = Storage::disk(self::PRIVATE_DISK);
                $book->file_size = $disk->exists($book->file_path) ? $disk->size($book->file_path) : null;
            }

            if (Auth::check()) {
                $book->updated_by = Auth::id();
                $book->created_by ??= Auth::id();
            }
        });

        static::forceDeleted(function (Book $book): void {
            if ($book->file_path) {
                Storage::disk(self::PRIVATE_DISK)->delete($book->file_path);
            }

            if ($book->sample_path) {
                Storage::disk('public')->delete($book->sample_path);
            }
        });
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BookCategory::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BookTag::class);
    }

    public function gallery(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'book_media')->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_id');
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', BookStatus::Published)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

        return $query->where(fn (Builder $q) => $q
            ->where('title', 'like', $like)
            ->orWhere('subtitle', 'like', $like)
            ->orWhere('short_description', 'like', $like)
            ->orWhere('sku', 'like', $like)
            ->orWhere('isbn', 'like', $like)
            ->orWhereHas('tags', fn (Builder $t) => $t->where('name', 'like', $like)));
    }

    public function isPublished(): bool
    {
        return $this->status === BookStatus::Published && ($this->published_at === null || $this->published_at->isPast());
    }

    public function effectivePriceCents(): ?int
    {
        if ($this->sale_price_cents !== null && $this->price_cents !== null && $this->sale_price_cents < $this->price_cents) {
            return $this->sale_price_cents;
        }

        return $this->price_cents;
    }

    public function hasDownloadFile(): bool
    {
        return filled($this->file_path) && Storage::disk(self::PRIVATE_DISK)->exists($this->file_path);
    }

    /**
     * A product can be bought on-site when it is live, priced, has a deliverable
     * file and is not sold through an external retailer.
     */
    public function isPurchasable(): bool
    {
        return $this->isPublished()
            && blank($this->external_purchase_url)
            && ($this->effectivePriceCents() ?? 0) > 0
            && $this->hasDownloadFile();
    }

    public function isFree(): bool
    {
        return $this->price_cents === null || $this->price_cents === 0;
    }

    public function revalidationTags(): array
    {
        return ['books', 'book:'.$this->slug, 'book-categories'];
    }
}
