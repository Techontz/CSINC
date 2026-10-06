<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Enums\MessageStatus;
use App\Filament\Resources\ContactMessages\Concerns\MessageActions;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

/**
 * @property ContactMessage $record
 */
class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->status === MessageStatus::New && Auth::user()->can('update', $this->record)) {
            $this->record->markAs(MessageStatus::Read, Auth::user());
        }
    }

    public function getTitle(): string
    {
        return $this->record->full_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label('Reply by email')
                ->icon('heroicon-m-paper-airplane')
                ->url(fn (): string => 'mailto:'.$this->record->email.'?subject='.rawurlencode('Re: '.($this->record->subject ?: 'Your inquiry to CSinc91'))),
            MessageActions::setStatus(MessageStatus::Replied, 'Mark as replied', 'heroicon-m-check-circle')->color('success'),
            Action::make('note')
                ->label('Add note')
                ->icon('heroicon-m-pencil-square')
                ->color('gray')
                ->authorize(fn (): bool => Auth::user()->can('update', $this->record))
                ->fillForm(fn (): array => ['internal_notes' => $this->record->internal_notes])
                ->schema([Textarea::make('internal_notes')->label('Internal notes')->rows(6)->maxLength(5000)])
                ->action(function (array $data): void {
                    $this->record->update(['internal_notes' => $data['internal_notes']]);
                    Notification::make()->success()->title('Notes saved')->send();
                }),
            ActionGroup::make([
                MessageActions::setStatus(MessageStatus::New, 'Mark as unread', 'heroicon-m-envelope'),
                MessageActions::setStatus(MessageStatus::Archived, 'Archive', 'heroicon-m-archive-box'),
                DeleteAction::make(),
                RestoreAction::make(),
            ])->icon('heroicon-m-ellipsis-horizontal')->color('gray')->button()->label('More'),
        ];
    }
}
