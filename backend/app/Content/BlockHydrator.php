<?php

namespace App\Content;

use App\Enums\ServiceGroup;
use App\Http\Resources\V1\BookCategoryResource;
use App\Http\Resources\V1\BookResource;
use App\Http\Resources\V1\MediaResource;
use App\Http\Resources\V1\ServiceResource;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Media;
use App\Models\Service;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Turns stored page blocks into render-ready payloads for the public API:
 * media ids become image objects, dynamic blocks embed live data, rich text
 * is sanitized and {{placeholders}} resolve to current catalogue figures.
 */
class BlockHydrator
{
    /** Block keys holding a single media id. */
    private const MEDIA_KEYS = ['image_id', 'background_id', 'video_id'];

    /** Block keys holding editor HTML. */
    private const HTML_KEYS = ['body_html', 'answer'];

    /** @var Collection<int, Media> */
    private Collection $media;

    /** @var array<string, string>|null */
    private ?array $tokens = null;

    public function __construct(private Request $request) {}

    /**
     * @param  list<array{type: string, data: array<string, mixed>}>|null  $blocks
     * @return list<array{type: string, id: string, data: array<string, mixed>}>
     */
    public function hydrate(?array $blocks): array
    {
        $blocks = array_values(array_filter($blocks ?? [], fn ($block): bool => is_array($block) && isset($block['type'])));

        $this->media = Media::query()->whereIn('id', $this->collectMediaIds($blocks))->get()->keyBy('id');

        return array_map(fn (array $block, int $index): array => [
            'type' => $block['type'],
            'id' => $block['type'].'-'.$index,
            'data' => $this->hydrateBlock($block['type'], $block['data'] ?? []),
        ], $blocks, array_keys($blocks));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function hydrateBlock(string $type, array $data): array
    {
        $data = $this->hydrateValues($data);

        return match ($type) {
            'services', 'service_links' => [...$data, 'services' => $this->services($data['group'] ?? null)],
            'books' => [...$data, 'books' => $this->books($data)],
            'book_categories' => [...$data, 'categories' => $this->categories()],
            default => $data,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function hydrateValues(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (in_array($key, self::MEDIA_KEYS, true)) {
                $media = is_numeric($value) ? $this->media->get((int) $value) : null;
                $result[str_replace('_id', '', $key)] = $media ? MediaResource::make($media)->resolve($this->request) : null;

                continue;
            }

            if (in_array($key, self::HTML_KEYS, true) && is_string($value)) {
                $result[$key] = RichText::clean($this->replaceTokens($value));

                continue;
            }

            $result[$key] = match (true) {
                is_array($value) => $this->hydrateValues($value),
                is_string($value) => $this->replaceTokens($value),
                default => $value,
            };
        }

        return $result;
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     * @return list<int>
     */
    private function collectMediaIds(array $blocks): array
    {
        $ids = [];

        array_walk_recursive($blocks, function ($value, $key) use (&$ids): void {
            if (in_array($key, self::MEDIA_KEYS, true) && is_numeric($value)) {
                $ids[] = (int) $value;
            }
        });

        return array_values(array_unique($ids));
    }

    private function replaceTokens(string $value): string
    {
        if (! str_contains($value, '{{')) {
            return $value;
        }

        $this->tokens ??= $this->buildTokens();

        return strtr($value, $this->tokens);
    }

    /**
     * @return array<string, string>
     */
    private function buildTokens(): array
    {
        $tokens = ['{{products.count}}' => (string) Book::query()->published()->count()];

        BookCategory::query()->published()
            ->withCount(['books' => fn ($query) => $query->published()])
            ->get()
            ->each(function (BookCategory $category) use (&$tokens): void {
                $tokens['{{category.'.$category->slug.'.count}}'] = (string) $category->books_count;
            });

        return $tokens;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function services(?string $group): array
    {
        $query = Service::query()->published()->ordered()->with('image');

        if ($group && ServiceGroup::tryFrom($group)) {
            $query->where('group', $group);
        }

        return ServiceResource::collection($query->get())->resolve($this->request);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    private function books(array $data): array
    {
        $limit = max(1, min(24, (int) ($data['limit'] ?? 8)));
        $query = Book::query()->published()->with(['cover', 'categories']);

        match ($data['source'] ?? 'featured') {
            'latest' => $query->latest('published_at'),
            'category' => $query->whereHas('categories', fn ($q) => $q->where('slug', $data['category'] ?? null))->ordered(),
            default => $query->where('is_featured', true)->ordered(),
        };

        return BookResource::collection($query->limit($limit)->get())->resolve($this->request);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function categories(): array
    {
        $categories = BookCategory::query()->published()->ordered()->with('image')
            ->withCount(['books' => fn ($query) => $query->published()])
            ->get();

        return BookCategoryResource::collection($categories)->resolve($this->request);
    }
}
