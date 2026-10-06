<?php

namespace App\Filament\Resources\Books\Tables;

use App\Enums\BookStatus;
use App\Filament\Resources\Books\BookResource;
use App\Models\Book;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        $canPublish = fn (): bool => (bool) Auth::user()?->can('publish', Book::class);

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['cover', 'categories']))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->searchPlaceholder('Search title, SKU…')
            ->columns([
                ImageColumn::make('cover.path')
                    ->label('')
                    ->disk('public')
                    ->imageWidth(40)
                    ->imageHeight(56)
                    ->extraImgAttributes(['class' => 'rounded-sm object-cover shadow-sm', 'loading' => 'lazy'])
                    ->defaultImageUrl(url('/brand/csinc91-icon.svg')),
                TextColumn::make('title')
                    ->searchable(['title', 'sku', 'subtitle'])
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->description(fn (Book $record): ?string => $record->sku)
                    ->wrap(),
                TextColumn::make('categories.name')
                    ->label('Category')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('price_cents')
                    ->label('Price')
                    ->money(fn (Book $record): string => $record->currency, divideBy: 100)
                    ->description(fn (Book $record): ?string => $record->sale_price_cents !== null ? 'Sale: '.number_format($record->sale_price_cents / 100, 2) : null)
                    ->sortable()
                    ->placeholder('Free'),
                IconColumn::make('file_path')
                    ->label('File')
                    ->tooltip(fn (Book $record): string => $record->file_path ? 'Download file attached' : 'No download file — cannot be purchased')
                    ->state(fn (Book $record): bool => filled($record->file_path))
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->falseColor('warning'),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->disabled(fn (): bool => ! $canPublish()),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(BookStatus::class),
                SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('is_featured')->label('Featured'),
                TernaryFilter::make('has_file')
                    ->label('Download file')
                    ->trueLabel('Attached')
                    ->falseLabel('Missing')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('file_path'),
                        false: fn (Builder $query) => $query->whereNull('file_path'),
                    ),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->tooltip('Edit'),
                ActionGroup::make([
                    Action::make('view')
                        ->label('View on website')
                        ->icon('heroicon-m-arrow-top-right-on-square')
                        ->url(fn (Book $record): string => BookResource::publicUrl($record), shouldOpenInNewTab: true)
                        ->visible(fn (Book $record): bool => $record->isPublished()),
                    Action::make('togglePublished')
                        ->label(fn (Book $record): string => $record->status === BookStatus::Published ? 'Unpublish' : 'Publish')
                        ->icon(fn (Book $record): string => $record->status === BookStatus::Published ? 'heroicon-m-eye-slash' : 'heroicon-m-globe-alt')
                        ->authorize('publish', Book::class)
                        ->requiresConfirmation()
                        ->action(function (Book $record): void {
                            $record->update(['status' => $record->status === BookStatus::Published ? BookStatus::Draft : BookStatus::Published]);
                            Notification::make()->success()->title('“'.$record->title.'” is now '.$record->status->getLabel())->send();
                        })
                        ->hidden(fn (Book $record): bool => $record->trashed()),
                    DeleteAction::make()->label('Move to trash'),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::statusBulkAction('publish', 'Publish', BookStatus::Published, 'heroicon-m-globe-alt'),
                    self::statusBulkAction('unpublish', 'Unpublish', BookStatus::Draft, 'heroicon-m-eye-slash'),
                    self::statusBulkAction('archive', 'Archive', BookStatus::Archived, 'heroicon-m-archive-box'),
                    BulkAction::make('feature')
                        ->label('Feature on homepage')
                        ->icon('heroicon-m-star')
                        ->authorize('publish', Book::class)
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unfeature')
                        ->label('Remove from homepage')
                        ->icon('heroicon-m-minus-circle')
                        ->authorize('publish', Book::class)
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => false]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()->label('Move to trash'),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-book-open')
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Create your first startup guide. It will appear on the website as soon as it is published.');
    }

    private static function statusBulkAction(string $name, string $label, BookStatus $status, string $icon): BulkAction
    {
        return BulkAction::make($name)
            ->label($label)
            ->icon($icon)
            ->authorize('publish', Book::class)
            ->requiresConfirmation()
            ->modalDescription("Set the selected products to “{$status->getLabel()}”?")
            ->action(function (Collection $records) use ($status, $label): void {
                $records->each->update(['status' => $status]);
                Notification::make()->success()->title($label.': '.$records->count().' product(s) updated')->send();
            })
            ->deselectRecordsAfterCompletion();
    }
}
