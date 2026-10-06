<?php

namespace App\Http\Controllers\Api\V1;

use App\Commerce\OrderFulfillment;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\DownloadLinksResent;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    public function show(Request $request, Order $order, OrderFulfillment $fulfillment): JsonResponse
    {
        abort_unless($order->hasValidAccessToken($request->query('token')), 404);

        $order->load('items.book.cover');
        $canDownload = $order->status->grantsDownloads();

        return response()->json(['data' => [
            'uuid' => $order->uuid,
            'number' => $order->number,
            'status' => $order->status->value,
            'status_label' => $order->status->getLabel(),
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'total' => Money::format($order->total_cents, $order->currency),
            'created_at' => $order->created_at?->toIso8601String(),
            'paid_at' => $order->paid_at?->toIso8601String(),
            'items' => $order->items->map(fn (OrderItem $item): array => [
                'title' => $item->title,
                'slug' => $item->book?->slug,
                'price' => Money::format($item->unit_price_cents, $order->currency),
                'cover' => $item->book?->cover?->url,
                'download_url' => $canDownload && $item->book?->file_path ? $fulfillment->downloadUrl($item) : null,
            ])->values(),
        ]]);
    }

    /**
     * Emails fresh download links for every paid order on an address. The
     * response never reveals whether the address has purchases.
     */
    public function resend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'website' => ['nullable', 'max:0'],
        ]);

        $orders = Order::query()
            ->where('customer_email', mb_strtolower(trim($validated['email'])))
            ->where('status', 'paid')
            ->with('items')
            ->latest()
            ->limit(20)
            ->get();

        if ($orders->isNotEmpty()) {
            Notification::route('mail', $orders->first()->customer_email)->notify(new DownloadLinksResent($orders));
        }

        return response()->json([
            'message' => 'If we find purchases for that address, we will email fresh download links within a few minutes.',
        ]);
    }
}
