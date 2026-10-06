<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Filament\Resources\Media\Schemas\MediaForm;
use App\Media\MediaUploader;
use App\Models\Media;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected ?string $subheading = 'Upload once, reuse everywhere. Files are validated and stored with randomised names.';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Upload files')
                ->icon('heroicon-m-arrow-up-tray')
                ->visible(fn (): bool => Gate::allows('create', Media::class))
                ->modalHeading('Upload to media library')
                ->modalSubmitActionLabel('Add to library')
                ->schema([
                    Select::make('folder')->options(MediaForm::FOLDERS)->default('general')->required()->selectablePlaceholder(false),
                    FileUpload::make('files')
                        ->label('Files')
                        ->multiple()
                        ->maxFiles(20)
                        ->disk('public')
                        ->directory('media/uploads/'.now()->format('Y/m'))
                        ->acceptedFileTypes([...MediaUploader::IMAGE_MIMES, ...MediaUploader::VIDEO_MIMES, ...MediaUploader::DOCUMENT_MIMES])
                        ->maxSize(MediaUploader::MAX_DOCUMENT_KB)
                        ->storeFileNamesIn('original_names')
                        ->panelLayout('grid')
                        ->required()
                        ->helperText('JPG, PNG, WebP, AVIF, GIF, MP4, WebM or PDF — up to 50 MB.'),
                ])
                ->action(function (array $data): void {
                    $uploader = app(MediaUploader::class);
                    $names = $data['original_names'] ?? [];

                    foreach ($data['files'] as $path) {
                        $name = $names[$path] ?? basename($path);
                        $uploader->registerExisting($path, $data['folder'], $name, [
                            'title' => pathinfo($name, PATHINFO_FILENAME),
                            'uploaded_by' => Auth::id(),
                        ]);
                    }

                    Notification::make()->success()->title(count($data['files']).' file(s) added to the library')->send();
                }),
        ];
    }
}
