<?php

namespace App\Media;

use App\Models\Book;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Central place for persisting uploads: randomised filenames, typed folders
 * and recorded metadata so files can be reused from the media library.
 */
class MediaUploader
{
    /** @var list<string> */
    public const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif'];

    /** @var list<string> */
    public const DOCUMENT_MIMES = ['application/pdf'];

    /** @var list<string> */
    public const VIDEO_MIMES = ['video/mp4', 'video/webm'];

    public const MAX_IMAGE_KB = 8192;

    public const MAX_VIDEO_KB = 51200;

    public const MAX_DOCUMENT_KB = 51200;

    public function storeMedia(UploadedFile $file, string $folder = 'general', ?User $user = null, array $attributes = []): Media
    {
        $path = $file->store('media/'.$folder.'/'.now()->format('Y/m'), 'public');

        return Media::query()->create([
            'disk' => 'public',
            'path' => $path,
            'original_name' => str($file->getClientOriginalName())->limit(240, '')->toString(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize() ?: 0,
            ...$this->dimensions(Storage::disk('public')->path($path)),
            'folder' => $folder,
            'uploaded_by' => $user?->getKey(),
            ...$attributes,
        ]);
    }

    /**
     * Stores a paid download on the private disk; it is only ever streamed
     * through signed, order-checked download routes.
     *
     * @return array{file_path: string, file_original_name: string, file_size: int}
     */
    public function storeProductFile(UploadedFile $file): array
    {
        return [
            'file_path' => $file->store('books/files', Book::PRIVATE_DISK),
            'file_original_name' => str($file->getClientOriginalName())->limit(240, '')->toString(),
            'file_size' => (int) $file->getSize(),
        ];
    }

    public function storeSample(UploadedFile $file): string
    {
        return $file->store('books/samples', 'public');
    }

    /**
     * Registers an existing file on the public disk in the library.
     */
    public function registerExisting(string $path, string $folder, ?string $originalName = null, array $attributes = []): Media
    {
        $disk = Storage::disk('public');

        return Media::query()->firstOrCreate(['path' => $path], [
            'disk' => 'public',
            'original_name' => $originalName ?? basename($path),
            'mime_type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'size' => $disk->size($path),
            ...$this->dimensions($disk->path($path)),
            'folder' => $folder,
            ...$attributes,
        ]);
    }

    /**
     * @return array{width?: int, height?: int}
     */
    private function dimensions(string $absolutePath): array
    {
        $info = @getimagesize($absolutePath);

        return $info ? ['width' => (int) $info[0], 'height' => (int) $info[1]] : [];
    }
}
