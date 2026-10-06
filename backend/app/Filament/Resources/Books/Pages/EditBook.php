<?php

namespace App\Filament\Resources\Books\Pages;

use App\Enums\BookStatus;
use App\Filament\Resources\Books\BookResource;
use App\Filament\Resources\Books\Concerns\GuardsPublishing;
use App\Models\Book;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

/**
 * @property Book $record
 */
class EditBook extends EditRecord
{
    use GuardsPublishing;

    protected static string $resource = BookResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->guardPublishing($data, $this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('View on website')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => BookResource::publicUrl($this->record), shouldOpenInNewTab: true)
                ->visible(fn (): bool => $this->record->isPublished()),
            Action::make('publish')
                ->label('Publish')
                ->icon('heroicon-m-globe-alt')
                ->visible(fn (): bool => ! $this->record->isPublished() && ! $this->record->trashed())
                ->authorize('publish', Book::class)
                ->requiresConfirmation()
                ->modalDescription('The product will appear on the public website immediately.')
                ->action(function (): void {
                    $this->record->update(['status' => BookStatus::Published, 'published_at' => $this->record->published_at?->isFuture() ? now() : ($this->record->published_at ?? now())]);
                    $this->refreshFormData(['status', 'published_at']);
                    Notification::make()->success()->title('Product published')->send();
                }),
            ActionGroup::make([
                Action::make('unpublish')
                    ->label('Unpublish')
                    ->icon('heroicon-m-eye-slash')
                    ->visible(fn (): bool => $this->record->status === BookStatus::Published)
                    ->authorize('publish', Book::class)
                    ->requiresConfirmation()
                    ->modalDescription('The product will be removed from the public website. Existing customers keep access to their downloads.')
                    ->action(function (): void {
                        $this->record->update(['status' => BookStatus::Draft]);
                        $this->refreshFormData(['status']);
                        Notification::make()->success()->title('Product unpublished')->send();
                    }),
                ReplicateAction::make()
                    ->label('Duplicate')
                    ->excludeAttributes(['slug', 'sku', 'file_path', 'file_original_name', 'file_size', 'published_at'])
                    ->beforeReplicaSaved(fn (Book $replica) => $replica->fill([
                        'title' => $replica->title.' (copy)',
                        'slug' => Str::slug($replica->title.'-copy-'.Str::lower(Str::random(4))),
                        'status' => BookStatus::Draft,
                        'is_featured' => false,
                    ]))
                    ->after(fn (Book $replica) => $replica->categories()->sync($this->record->categories->modelKeys()))
                    ->successRedirectUrl(fn (Book $replica): string => BookResource::getUrl('edit', ['record' => $replica])),
                DeleteAction::make()->label('Move to trash'),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])->icon('heroicon-m-ellipsis-horizontal')->color('gray')->button()->label('More'),
        ];
    }
}
