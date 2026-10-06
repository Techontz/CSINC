<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Asks the Next.js frontend to purge cached fetches tagged with the given tags.
 */
class RevalidateFrontend implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  list<string>  $tags
     */
    public function __construct(public array $tags) {}

    public function handle(): void
    {
        $url = config('services.frontend.url');
        $secret = config('services.frontend.revalidate_secret');

        if (blank($url) || blank($secret) || app()->runningUnitTests()) {
            return;
        }

        try {
            Http::timeout(5)
                ->withHeaders(['x-revalidate-secret' => $secret])
                ->post(rtrim($url, '/').'/api/revalidate', ['tags' => array_values(array_unique($this->tags))]);
        } catch (Throwable $exception) {
            Log::warning('Frontend revalidation failed', ['tags' => $this->tags, 'error' => $exception->getMessage()]);
        }
    }
}
