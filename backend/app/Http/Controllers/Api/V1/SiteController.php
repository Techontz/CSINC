<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\NavigationLocation;
use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use App\Settings\SiteSettings;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    public function __invoke(SiteSettings $settings): JsonResponse
    {
        $items = NavigationItem::query()
            ->visible()
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->visible()])
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn (NavigationItem $item): string => $item->location->value);

        $navigation = [];

        foreach (NavigationLocation::cases() as $location) {
            $navigation[$location->value] = ($items[$location->value] ?? collect())
                ->map(fn (NavigationItem $item): array => $this->navigationPayload($item))
                ->values();
        }

        return response()->json(['data' => [...$settings->publicPayload(), 'navigation' => $navigation]]);
    }

    /**
     * @return array<string, mixed>
     */
    private function navigationPayload(NavigationItem $item): array
    {
        return [
            'label' => $item->label,
            'url' => $item->url,
            'description' => $item->description,
            'new_tab' => $item->open_in_new_tab,
            'children' => $item->relationLoaded('children')
                ? $item->children->map(fn (NavigationItem $child): array => $this->navigationPayload($child))->values()
                : [],
        ];
    }
}
