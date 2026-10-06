<?php

namespace App\Http\Resources\V1;

use App\Models\Book;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Full product representation for the detail page.
 *
 * @mixin Book
 */
class BookDetailResource extends BookResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $ogImage = $this->ogImage ?? $this->cover;

        return [
            ...parent::toArray($request),
            'description' => RichText::clean($this->description),
            'author' => $this->author,
            'co_authors' => array_values(array_filter($this->co_authors ?? [])),
            'isbn' => $this->isbn,
            'publisher' => $this->publisher,
            'publication_date' => $this->publication_date?->toDateString(),
            'edition' => $this->edition,
            'language' => $this->language,
            'page_count' => $this->page_count,
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($tag): array => ['name' => $tag->name, 'slug' => $tag->slug])->values()),
            'gallery' => MediaResource::collection($this->whenLoaded('gallery')),
            'sample_url' => $this->sample_path ? Storage::disk('public')->url($this->sample_path) : null,
            'has_download' => filled($this->file_path),
            'seo' => [
                'title' => $this->seo_title ?: $this->title,
                'description' => $this->seo_description
                    ?: $this->short_description
                    ?: RichText::plain($this->description),
                'image' => $ogImage ? MediaResource::make($ogImage) : null,
            ],
        ];
    }
}
