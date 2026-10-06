<?php

namespace App\Http\Resources\V1;

use App\Models\Service;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Service
 */
class ServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'group' => $this->group->value,
            'group_label' => $this->group->getLabel(),
            'summary' => $this->summary,
            'body' => RichText::clean($this->body),
            'highlights' => array_values(array_filter($this->highlights ?? [])),
            'image' => MediaResource::make($this->whenLoaded('image')),
            'link' => $this->link_url ? ['label' => $this->link_label ?: 'Learn more', 'url' => $this->link_url] : null,
        ];
    }
}
