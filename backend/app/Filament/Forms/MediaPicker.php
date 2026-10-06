<?php

namespace App\Filament\Forms;

use App\Media\MediaUploader;
use App\Models\Media;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Select an image (or video) from the media library, or upload a new one in place.
 */
class MediaPicker
{
    public static function make(string $name, string $folder = 'general', string $type = 'image'): Select
    {
        $isVideo = $type === 'video';
        $scope = fn (Builder $query): Builder => $isVideo ? $query->videos() : $query->images();

        $upload = FileUpload::make('file')
            ->label($isVideo ? 'Video (MP4 or WebM)' : 'Image')
            ->disk('public')
            ->directory('media/'.$folder.'/'.now()->format('Y/m'))
            ->acceptedFileTypes($isVideo ? MediaUploader::VIDEO_MIMES : MediaUploader::IMAGE_MIMES)
            ->maxSize($isVideo ? MediaUploader::MAX_VIDEO_KB : MediaUploader::MAX_IMAGE_KB)
            ->required();

        if (! $isVideo) {
            $upload->image()->imageEditor();
        } else {
            $upload->helperText('Short, muted loops of 10–20 seconds at 1600–1920px wide work best (under 5 MB).');
        }

        return Select::make($name)
            ->allowHtml()
            ->searchable()
            ->native(false)
            ->getSearchResultsUsing(fn (?string $search): array => $scope(Media::query())
                ->when($search, fn ($query) => $query->where(fn ($q) => $q
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('alt', 'like', "%{$search}%")
                    ->orWhere('original_name', 'like', "%{$search}%")))
                ->latest()
                ->limit(40)
                ->get()
                ->mapWithKeys(fn (Media $media): array => [$media->getKey() => self::optionHtml($media)])
                ->all())
            ->options(fn (): array => $scope(Media::query())
                ->when(! $isVideo, fn ($query) => $query->where('folder', $folder))
                ->latest()
                ->limit(30)
                ->get()
                ->mapWithKeys(fn (Media $media): array => [$media->getKey() => self::optionHtml($media)])
                ->all())
            ->getOptionLabelUsing(fn ($value): ?string => ($media = Media::query()->find($value)) ? self::optionHtml($media) : null)
            ->createOptionForm([
                $upload,
                TextInput::make('alt')
                    ->label($isVideo ? 'Description' : 'Alternative text')
                    ->helperText($isVideo ? 'Briefly describe the footage.' : 'Describe the image for screen readers and search engines.')
                    ->maxLength(190)
                    ->required(),
            ])
            ->createOptionUsing(fn (array $data): int => app(MediaUploader::class)
                ->registerExisting($data['file'], $folder, null, [
                    'alt' => $data['alt'],
                    'title' => $data['alt'],
                    'uploaded_by' => Auth::id(),
                ])
                ->getKey())
            ->createOptionModalHeading($isVideo ? 'Upload a video' : 'Upload to media library');
    }

    public static function optionHtml(Media $media): string
    {
        $label = e(str($media->title ?: $media->original_name)->limit(30)->toString());
        $meta = $media->width ? e($media->width.'×'.$media->height) : e(strtoupper(str($media->mime_type)->after('/')->toString()));

        $thumb = $media->isVideo()
            ? '<video src="'.e($media->url).'" muted preload="metadata" class="h-9 w-12 shrink-0 rounded object-cover ring-1 ring-slate-200"></video>'
            : '<img src="'.e($media->url).'" alt="" class="h-9 w-12 shrink-0 rounded object-cover ring-1 ring-slate-200" loading="lazy">';

        return '<span class="flex items-center gap-3">'.$thumb.'<span class="min-w-0"><span class="block truncate text-sm">'.$label.'</span><span class="block text-xs text-slate-400">'.$meta.'</span></span></span>';
    }
}
