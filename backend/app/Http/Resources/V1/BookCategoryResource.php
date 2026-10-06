<?php

namespace App\Http\Resources\V1;

use App\Models\BookCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BookCategory
 */
class BookCategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'headline' => $this->headline,
            'description' => $this->description,
            'image' => MediaResource::make($this->whenLoaded('image')),
            'books_count' => $this->whenCounted('books'),
            'seo' => [
                'title' => $this->seo_title ?: $this->name,
                'description' => $this->seo_description ?: ($this->description ? str($this->description)->limit(160)->toString() : null),
            ],
        ];
    }
}
