<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\BookStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\BookRequest;
use App\Http\Resources\V1\Admin\AdminBookResource;
use App\Media\MediaUploader;
use App\Models\Book;
use App\Models\BookTag;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class BookController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Book::class);

        $validated = $request->validate([
            'status' => ['nullable', 'in:draft,published,archived,trashed'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $books = Book::query()
            ->with(['cover', 'categories'])
            ->when(($validated['status'] ?? null) === 'trashed', fn ($q) => $q->onlyTrashed())
            ->when(in_array($validated['status'] ?? null, ['draft', 'published', 'archived'], true), fn ($q) => $q->where('status', $validated['status']))
            ->search($validated['search'] ?? null)
            ->ordered()
            ->paginate($validated['per_page'] ?? 25);

        return AdminBookResource::collection($books);
    }

    public function show(Book $book): AdminBookResource
    {
        Gate::authorize('view', $book);

        return AdminBookResource::make($book->load(['cover', 'ogImage', 'gallery', 'tags', 'categories']));
    }

    public function store(BookRequest $request): JsonResponse
    {
        $book = $this->persist(new Book, $request);

        return AdminBookResource::make($book)->response()->setStatusCode(201);
    }

    public function update(BookRequest $request, Book $book): AdminBookResource
    {
        return AdminBookResource::make($this->persist($book, $request));
    }

    public function publish(Book $book): AdminBookResource
    {
        Gate::authorize('publish', Book::class);

        $book->update(['status' => BookStatus::Published, 'published_at' => $book->published_at ?? now()]);

        return AdminBookResource::make($book->load(['cover', 'categories', 'tags']));
    }

    public function unpublish(Book $book): AdminBookResource
    {
        Gate::authorize('publish', Book::class);

        $book->update(['status' => BookStatus::Draft]);

        return AdminBookResource::make($book->load(['cover', 'categories', 'tags']));
    }

    public function destroy(Book $book): JsonResponse
    {
        Gate::authorize('delete', $book);

        $book->delete();

        return response()->json(status: 204);
    }

    private function persist(Book $book, BookRequest $request): Book
    {
        $data = $request->safe()->except(['cover', 'file', 'sample', 'category_ids', 'tags']);

        $this->guardPublishing($book, $request, $data);

        return DB::transaction(function () use ($book, $request, $data): Book {
            if ($request->hasFile('cover')) {
                $media = $this->uploader->storeMedia($request->file('cover'), 'covers', $request->user(), ['alt' => $data['title'] ?? $book->title]);
                $data['cover_id'] = $media->getKey();
            }

            if ($request->hasFile('file')) {
                $data = [...$data, ...$this->uploader->storeProductFile($request->file('file'))];
            }

            if ($request->hasFile('sample')) {
                $data['sample_path'] = $this->uploader->storeSample($request->file('sample'));
            }

            $book->fill(Arr::where($data, fn ($value, $key): bool => $value !== null || $book->exists))->save();

            if ($request->has('category_ids')) {
                $book->categories()->sync($request->validated('category_ids') ?? []);
            }

            if ($request->has('tags')) {
                $tagIds = collect($request->validated('tags') ?? [])
                    ->map(fn (string $name): int => BookTag::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->getKey());
                $book->tags()->sync($tagIds);
            }

            return $book->refresh()->load(['cover', 'ogImage', 'gallery', 'tags', 'categories']);
        });
    }

    /**
     * Editors may draft and edit, but only publishers can change visibility.
     *
     * @param  array<string, mixed>  $data
     */
    private function guardPublishing(Book $book, BookRequest $request, array $data): void
    {
        $changesVisibility = (array_key_exists('status', $data) && $data['status'] !== ($book->status?->value ?? BookStatus::Draft->value))
            || (array_key_exists('is_featured', $data) && (bool) $data['is_featured'] !== (bool) $book->is_featured);

        if ($changesVisibility && ! $request->user()->can('publish', Book::class)) {
            throw new AuthorizationException('You are not allowed to publish, unpublish or feature products.');
        }
    }
}
