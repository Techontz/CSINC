<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    /**
     * Streams a purchased file. Access requires a valid temporary signature
     * (enforced by middleware) and a paid order.
     */
    public function show(OrderItem $orderItem): StreamedResponse
    {
        $orderItem->load(['order', 'book']);
        $book = $orderItem->book;

        abort_unless($orderItem->order->status->grantsDownloads(), 403, 'This order is not eligible for downloads.');
        abort_if(! $book || blank($book->file_path) || ! Storage::disk(Book::PRIVATE_DISK)->exists($book->file_path), 404);

        $orderItem->increment('download_count', 1, ['last_downloaded_at' => now()]);

        $extension = pathinfo($book->file_path, PATHINFO_EXTENSION) ?: 'pdf';
        $filename = str($book->title)->slug()->append('.', $extension)->toString();

        return Storage::disk(Book::PRIVATE_DISK)->download($book->file_path, $filename, [
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
