<?php

namespace App\Filament\Resources\Media\Schemas;

use App\Models\Media;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class MediaForm
{
    /** @var array<string, string> */
    public const FOLDERS = [
        'general' => 'General',
        'site' => 'Website imagery',
        'covers' => 'Product covers',
        'categories' => 'Categories',
        'seo' => 'Social sharing',
        'brand' => 'Brand',
        'documents' => 'Documents',
        'hero' => 'Hero videos',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('preview')
                ->hiddenLabel()
                ->columnSpanFull()
                ->state(fn (?Media $record): ?HtmlString => match (true) {
                    (bool) $record?->isImage() => new HtmlString('<img src="'.e($record->url).'" alt="" class="max-h-72 w-full rounded-lg bg-slate-50 object-contain ring-1 ring-slate-200">'),
                    (bool) $record?->isVideo() => new HtmlString('<video src="'.e($record->url).'" controls muted class="max-h-72 w-full rounded-lg bg-slate-900"></video>'),
                    default => null,
                }),
            TextInput::make('title')->maxLength(190),
            TextInput::make('alt')
                ->label('Alternative text')
                ->maxLength(190)
                ->helperText('Describes the image for screen readers and search engines.'),
            Select::make('folder')->options(self::FOLDERS)->required()->selectablePlaceholder(false),
            TextEntry::make('details')
                ->label('File')
                ->state(fn (?Media $record): ?string => $record
                    ? $record->original_name.' · '.$record->mime_type.' · '.Number::fileSize($record->size).($record->width ? ' · '.$record->width.'×'.$record->height : '')
                    : null),
        ]);
    }
}
