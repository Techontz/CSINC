<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PageResource;
use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug): PageResource
    {
        $page = Page::query()->published()->with('ogImage')->where('slug', $slug)->firstOrFail();

        return PageResource::make($page);
    }
}
