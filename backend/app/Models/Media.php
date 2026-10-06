<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\RevalidatesFrontend;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory, LogsActivity, RevalidatesFrontend;

    protected $table = 'media';

    protected $fillable = [
        'disk', 'path', 'original_name', 'mime_type', 'size', 'width', 'height',
        'title', 'alt', 'folder', 'uploaded_by',
    ];

    protected $appends = ['url'];

    protected static function booted(): void
    {
        static::deleted(function (Media $media): void {
            Storage::disk($media->disk)->delete($media->path);
        });
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk($this->disk)->url($this->path));
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function scopeImages(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeVideos(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'video/%');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function revalidationTags(): array
    {
        return ['site', 'pages', 'books', 'services', 'book-categories'];
    }

    public function activityLabel(): string
    {
        return $this->title ?: $this->original_name;
    }

    /**
     * @return array{url: string, alt: string, width: int|null, height: int|null}
     */
    public function toImagePayload(?string $fallbackAlt = null): array
    {
        return [
            'url' => $this->url,
            'alt' => $this->alt ?: ($fallbackAlt ?? $this->title ?? ''),
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
