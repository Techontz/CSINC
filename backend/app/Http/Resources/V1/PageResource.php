<?php

namespace App\Http\Resources\V1;

use App\Content\BlockHydrator;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Page
 */
class PageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'eyebrow' => $this->eyebrow,
            'summary' => $this->summary,
            'blocks' => (new BlockHydrator($request))->hydrate($this->blocks),
            'seo' => [
                'title' => $this->seo_title ?: $this->title,
                'description' => $this->seo_description ?: $this->summary,
                'image' => $this->ogImage ? MediaResource::make($this->ogImage) : null,
            ],
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
