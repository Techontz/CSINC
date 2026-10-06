<?php

namespace Database\Seeders;

use App\Media\MediaUploader;
use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Imports CSinc91's existing site imagery into the media library.
 */
class MediaSeeder extends Seeder
{
    public function __construct(private MediaUploader $uploader) {}

    public function run(): void
    {
        $this->importHeroVideos();

        $items = json_decode((string) file_get_contents(database_path('data/media.json')), true);

        foreach ($items as $item) {
            if (isset($item['local'])) {
                $path = 'media/'.$item['folder'].'/'.basename($item['local']);
                Storage::disk('public')->put($path, (string) file_get_contents(database_path('data/'.$item['local'])));
                $this->uploader->registerExisting($path, $item['folder'], basename($item['local']), ['title' => $item['key'], 'alt' => $item['alt']]);

                continue;
            }

            self::import($this->uploader, $item['url'], 'media/'.$item['folder'].'/'.$item['key'], $item['folder'], [
                'title' => $item['key'],
                'alt' => $item['alt'],
            ], fn (string $message) => $this->command?->warn($message));
        }
    }

    /**
     * Registers the bundled hero background videos (Pexels License) and their posters.
     */
    private function importHeroVideos(): void
    {
        $disk = Storage::disk('public');

        foreach (['signing', 'business', 'healthcare', 'digital'] as $key) {
            foreach (["hero-{$key}.mp4" => "hero-video-{$key}", "hero-{$key}-poster.jpg" => "hero-poster-{$key}"] as $file => $title) {
                $path = 'media/hero/'.$file;

                if (! $disk->exists($path)) {
                    $disk->put($path, (string) file_get_contents(database_path('data/hero/'.$file)));
                }

                $this->uploader->registerExisting($path, 'hero', $file, ['title' => $title, 'alt' => self::HERO_ALT[$key]]);
            }
        }
    }

    private const HERO_ALT = [
        'signing' => 'Business owner signing documents',
        'business' => 'African team brainstorming in an office',
        'healthcare' => 'Caregiver supporting an elderly man at home',
        'digital' => 'Entrepreneur working on a laptop',
    ];

    /**
     * Downloads a remote file once and registers it in the library.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function import(MediaUploader $uploader, string $url, string $basePath, string $folder, array $attributes = [], ?callable $warn = null): ?Media
    {
        $disk = Storage::disk('public');
        $existing = collect($disk->files(dirname($basePath)))->first(fn (string $path): bool => str_starts_with($path, $basePath.'.'));

        if (! $existing) {
            try {
                $response = Http::timeout(30)->retry(2, 500)->withUserAgent('CSinc91-Importer/1.0')->get($url)->throw();
            } catch (Throwable $exception) {
                $warn && $warn("Skipped {$url}: {$exception->getMessage()}");

                return null;
            }

            $extension = match (true) {
                str_contains((string) $response->header('Content-Type'), 'png') => 'png',
                str_contains((string) $response->header('Content-Type'), 'webp') => 'webp',
                str_contains((string) $response->header('Content-Type'), 'avif') => 'avif',
                default => 'jpg',
            };

            $existing = $basePath.'.'.$extension;
            $disk->put($existing, $response->body());
        }

        return $uploader->registerExisting($existing, $folder, basename($url, '?'.parse_url($url, PHP_URL_QUERY)), $attributes);
    }

    public static function idFor(string $key): ?int
    {
        return Media::query()->where('title', $key)->value('id');
    }
}
