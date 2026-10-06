<?php

namespace App\Http\Resources\V1\Admin;

use App\Http\Resources\V1\BookDetailResource;
use App\Models\Book;
use Illuminate\Http\Request;

/**
 * @mixin Book
 */
class AdminBookResource extends BookDetailResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'status' => $this->status->value,
            'sort_order' => $this->sort_order,
            'price_cents' => $this->price_cents,
            'sale_price_cents' => $this->sale_price_cents,
            'file_original_name' => $this->file_original_name,
            'file_size' => $this->file_size,
            'category_ids' => $this->whenLoaded('categories', fn () => $this->categories->modelKeys()),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
