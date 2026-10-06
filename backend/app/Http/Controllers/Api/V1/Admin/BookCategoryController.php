<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BookCategoryResource;
use App\Models\BookCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BookCategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', BookCategory::class);

        return BookCategoryResource::collection(BookCategory::query()->ordered()->withCount('books')->get());
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', BookCategory::class);

        $category = BookCategory::query()->create($this->validated($request));

        return BookCategoryResource::make($category)->response()->setStatusCode(201);
    }

    public function update(Request $request, BookCategory $bookCategory): BookCategoryResource
    {
        Gate::authorize('update', $bookCategory);

        $bookCategory->update($this->validated($request, $bookCategory));

        return BookCategoryResource::make($bookCategory);
    }

    public function destroy(BookCategory $bookCategory): JsonResponse
    {
        Gate::authorize('delete', $bookCategory);

        $bookCategory->delete();

        return response()->json(status: 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?BookCategory $category = null): array
    {
        $required = $category ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$required, 'string', 'max:120'],
            'slug' => [$required, 'string', 'max:120', 'alpha_dash', Rule::unique('book_categories', 'slug')->ignore($category)],
            'headline' => ['nullable', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:320'],
        ]);
    }
}
