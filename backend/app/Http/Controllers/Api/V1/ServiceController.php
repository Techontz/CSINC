<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ServiceGroup;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'group' => ['nullable', Rule::enum(ServiceGroup::class)],
        ]);

        $services = Service::query()
            ->published()
            ->ordered()
            ->with('image')
            ->when($validated['group'] ?? null, fn ($query, $group) => $query->where('group', $group))
            ->get();

        return ServiceResource::collection($services);
    }
}
