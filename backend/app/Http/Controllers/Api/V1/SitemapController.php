<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

/**
 * Lightweight index of every public URL source for the frontend sitemap.
 */
class SitemapController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['data' => [
            'pages' => Page::query()->published()->get(['slug', 'updated_at'])
                ->map(fn (Page $page): array => ['slug' => $page->slug, 'updated_at' => $page->updated_at?->toIso8601String()]),
            'books' => Book::query()->published()->get(['slug', 'updated_at'])
                ->map(fn (Book $book): array => ['slug' => $book->slug, 'updated_at' => $book->updated_at?->toIso8601String()]),
            'categories' => BookCategory::query()->published()->get(['slug', 'updated_at'])
                ->map(fn (BookCategory $category): array => ['slug' => $category->slug, 'updated_at' => $category->updated_at?->toIso8601String()]),
        ]]);
    }
}
