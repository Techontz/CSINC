<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\MessageStatus;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ContactMessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', ContactMessage::class);

        $validated = $request->validate(['status' => ['nullable', Rule::enum(MessageStatus::class)]]);

        $messages = ContactMessage::query()
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(25, ['id', 'first_name', 'last_name', 'email', 'phone', 'subject', 'status', 'created_at']);

        return response()->json($messages);
    }

    public function show(ContactMessage $contactMessage): JsonResponse
    {
        Gate::authorize('view', $contactMessage);

        return response()->json(['data' => $contactMessage->makeHidden(['ip_address', 'user_agent'])]);
    }

    public function update(Request $request, ContactMessage $contactMessage): JsonResponse
    {
        Gate::authorize('update', $contactMessage);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(MessageStatus::class)],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (array_key_exists('internal_notes', $validated)) {
            $contactMessage->internal_notes = $validated['internal_notes'];
        }

        $contactMessage->markAs(MessageStatus::from($validated['status']), $request->user());

        return response()->json(['data' => $contactMessage->fresh()->makeHidden(['ip_address', 'user_agent'])]);
    }
}
