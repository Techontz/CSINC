<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BookCategoryResource;
use App\Models\BookCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookCategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = BookCategory::query()
            ->published()
            ->ordered()
            ->with('image')
            ->withCount(['books' => fn ($query) => $query->published()])
            ->get();

        return BookCategoryResource::collection($categories);
    }

    public function show(string $slug): BookCategoryResource
    {
        $category = BookCategory::query()
            ->published()
            ->with('image')
            ->withCount(['books' => fn ($query) => $query->published()])
            ->where('slug', $slug)
            ->firstOrFail();

        return BookCategoryResource::make($category);
    }
}
