<?php

namespace App\Http\Resources\V1;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'url' => $this->url,
            'alt' => (string) ($this->alt ?? $this->title ?? ''),
            'width' => $this->width,
            'height' => $this->height,
            'mime_type' => $this->mime_type,
        ];
    }
}
