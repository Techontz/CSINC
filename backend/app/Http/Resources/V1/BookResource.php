<?php

namespace App\Http\Resources\V1;

use App\Models\Book;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compact representation used in listings and grids.
 *
 * @mixin Book
 */
class BookResource extends JsonResource
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
            'subtitle' => $this->subtitle,
            'short_description' => $this->short_description,
            'sku' => $this->sku,
            'format' => $this->format,
            'cover' => MediaResource::make($this->whenLoaded('cover')),
            'price' => $this->pricePayload(),
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
            ])->values()),
            'is_featured' => $this->is_featured,
            'is_purchasable' => $this->isPurchasable(),
            'external_purchase_url' => $this->external_purchase_url,
            'published_at' => $this->published_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function pricePayload(): ?array
    {
        if ($this->price_cents === null) {
            return null;
        }

        $amount = $this->effectivePriceCents();

        return [
            'amount_cents' => $amount,
            'regular_cents' => $this->price_cents,
            'currency' => $this->currency,
            'formatted' => Money::format($amount, $this->currency),
            'formatted_regular' => Money::format($this->price_cents, $this->currency),
            'on_sale' => $amount !== $this->price_cents,
            'is_free' => $amount === 0,
        ];
    }
}
