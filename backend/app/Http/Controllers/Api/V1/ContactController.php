<?php

namespace App\Http\Controllers\Api\V1;

use App\Content\ConsultationForm;
use App\Enums\MessageStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContactRequest;
use App\Models\ContactMessage;
use App\Notifications\ContactMessageReceived;
use App\Settings\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class ContactController extends Controller
{
    public function options(): JsonResponse
    {
        return response()->json(['data' => ConsultationForm::options()]);
    }

    public function store(ContactRequest $request, SiteSettings $settings): JsonResponse
    {
        $validated = $request->validated();
        $address = array_filter($validated['address'] ?? []);

        $message = ContactMessage::query()->create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'company' => $validated['company'] ?? null,
            'subject' => $validated['topic'] ?? ($validated['product'] ?? 'Business consulting inquiry'),
            'message' => $validated['message'],
            'source' => filled($validated['product'] ?? null) ? 'product' : 'contact',
            'status' => MessageStatus::New,
            'details' => array_filter([
                'address' => $address ? implode(', ', $address) : null,
                'industry' => $validated['industry'] ?? null,
                'growth_stage' => $validated['growth_stage'],
                'challenges' => $validated['challenges'],
                'primary_goal' => $validated['primary_goal'] ?? null,
                'topic' => $validated['topic'] ?? null,
                'preferred_start_date' => $validated['preferred_start_date'],
                'service_of_interest' => $validated['service_of_interest'] ?? null,
                'preferred_service' => $validated['preferred_service'] ?? null,
                'product' => $validated['product'] ?? null,
                'signature' => $validated['signature'],
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => str((string) $request->userAgent())->limit(500, '')->toString(),
        ]);

        $recipient = $settings->get('contact.notification_email') ?: config('services.notifications.admin_email');

        if (filled($recipient)) {
            try {
                Notification::route('mail', $recipient)->notify(new ContactMessageReceived($message));
            } catch (Throwable $exception) {
                // The inquiry is safely stored; a mail outage must not fail the visitor's submission.
                Log::error('Contact notification failed', ['message_id' => $message->getKey(), 'error' => $exception->getMessage()]);
            }
        }

        return response()->json([
            'message' => 'Thank you — your inquiry has been received. Our team will be in touch shortly.',
        ], 201);
    }
}
