<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookIndexRequest;
use App\Http\Resources\V1\BookDetailResource;
use App\Http\Resources\V1\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    public function index(BookIndexRequest $request): AnonymousResourceCollection
    {
        $query = Book::query()
            ->published()
            ->with(['cover', 'categories' => fn ($q) => $q->published()->ordered()])
            ->search($request->validated('search'));

        if ($category = $request->validated('category')) {
            $query->whereHas('categories', fn ($q) => $q->published()->where('slug', $category));
        }

        if ($tag = $request->validated('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $tag));
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        match ($request->validated('sort') ?? 'default') {
            'newest' => $query->latest('published_at'),
            'title' => $query->orderBy('title'),
            'price_asc' => $query->orderByRaw('COALESCE(sale_price_cents, price_cents) is null, COALESCE(sale_price_cents, price_cents) asc'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price_cents, price_cents) desc'),
            default => $query->ordered(),
        };

        $perPage = (int) ($request->validated('per_page') ?? 24);

        return BookResource::collection($query->paginate($perPage)->withQueryString());
    }

    public function show(string $slug): JsonResponse
    {
        $book = Book::query()
            ->published()
            ->with(['cover', 'ogImage', 'gallery', 'tags', 'categories' => fn ($q) => $q->published()->ordered()])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Book::query()
            ->published()
            ->whereKeyNot($book->getKey())
            ->with(['cover', 'categories'])
            ->when($book->categories->isNotEmpty(), fn ($q) => $q->whereHas(
                'categories',
                fn ($c) => $c->whereIn('book_categories.id', $book->categories->modelKeys()),
            ))
            ->orderByDesc('is_featured')
            ->ordered()
            ->limit(4)
            ->get();

        return response()->json([
            'data' => BookDetailResource::make($book)->resolve(),
            'related' => BookResource::collection($related)->resolve(),
        ]);
    }
}
