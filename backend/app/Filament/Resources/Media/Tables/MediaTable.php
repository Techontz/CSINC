<?php

namespace App\Filament\Resources\Media\Tables;

use App\Filament\Resources\Media\Schemas\MediaForm;
use App\Models\Media;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->contentGrid(['sm' => 2, 'md' => 3, 'xl' => 5])
            ->paginated([20, 40, 80])
            ->defaultPaginationPageOption(40)
            ->columns([
                Stack::make([
                    ImageColumn::make('path')
                        ->disk('public')
                        ->imageHeight(160)
                        ->imageWidth('100%')
                        ->extraImgAttributes(fn (Media $record): array => [
                            'class' => 'w-full rounded-lg object-cover bg-slate-100 ring-1 ring-slate-200',
                            'style' => 'height:160px;width:100%',
                            'loading' => 'lazy',
                        ])
                        ->defaultImageUrl(url('/brand/csinc91-icon.svg')),
                    TextColumn::make('title')
                        ->weight(FontWeight::Medium)
                        ->limit(36)
                        ->searchable(['title', 'alt', 'original_name'])
                        ->extraAttributes(['class' => 'pt-3']),
                    TextColumn::make('mime_type')
                        ->formatStateUsing(fn (Media $record): string => strtoupper(str($record->mime_type)->after('/')).' · '.Number::fileSize($record->size).($record->width ? ' · '.$record->width.'×'.$record->height : ''))
                        ->color('gray')
                        ->size('xs'),
                ]),
            ])
            ->filters([
                SelectFilter::make('folder')->options(MediaForm::FOLDERS),
                SelectFilter::make('type')
                    ->options(['images' => 'Images', 'videos' => 'Videos', 'documents' => 'Documents'])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'images' => $query->where('mime_type', 'like', 'image/%'),
                        'videos' => $query->where('mime_type', 'like', 'video/%'),
                        'documents' => $query->where('mime_type', 'not like', 'image/%')->where('mime_type', 'not like', 'video/%'),
                        default => $query,
                    }),
            ])
            ->recordActions([
                Action::make('copy')
                    ->label('Copy URL')
                    ->icon('heroicon-m-link')
                    ->color('gray')
                    ->iconButton()
                    ->alpineClickHandler(fn (Media $record): string => 'navigator.clipboard.writeText('.json_encode($record->url).'); $tooltip(\'URL copied\', { timeout: 1500 })'),
                EditAction::make()->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->modalDescription('Pages and products using this image will fall back to having no image.'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateIcon('heroicon-o-photo')
            ->emptyStateHeading('Your media library is empty')
            ->emptyStateDescription('Upload images and documents to reuse them across pages and products.');
    }
}
