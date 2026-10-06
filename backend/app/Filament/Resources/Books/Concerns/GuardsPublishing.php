<?php

namespace App\Filament\Resources\Books\Concerns;

use App\Enums\BookStatus;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;

/**
 * Server-side enforcement that only users allowed to publish can change a
 * product's visibility, regardless of what the browser submits.
 */
trait GuardsPublishing
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function guardPublishing(array $data, ?Book $record = null): array
    {
        if (Auth::user()?->can('publish', Book::class)) {
            return $data;
        }

        $data['status'] = $record?->status ?? BookStatus::Draft;
        $data['is_featured'] = $record?->is_featured ?? false;
        $data['published_at'] = $record?->published_at;

        return $data;
    }
}
