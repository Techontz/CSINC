<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Content\ConsultationForm;
use App\Models\ContactMessage;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make(fn (ContactMessage $record): string => $record->subject ?: 'Inquiry')
                        ->description(fn (ContactMessage $record): string => 'Received '.$record->created_at->format('l, F j, Y \a\t g:i a'))
                        ->icon('heroicon-o-envelope-open')
                        ->schema([
                            TextEntry::make('message')
                                ->hiddenLabel()
                                ->prose()
                                ->formatStateUsing(fn (string $state): string => nl2br(e($state)))
                                ->html(),
                        ]),
                    Section::make('Consultation details')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema([
                            KeyValueEntry::make('details')
                                ->hiddenLabel()
                                ->keyLabel('Field')
                                ->valueLabel('Response')
                                ->state(fn (ContactMessage $record): array => collect(ConsultationForm::detailLabels())
                                    ->mapWithKeys(fn (string $label, string $key): array => [$label => $record->details[$key] ?? null])
                                    ->filter(fn ($value): bool => filled($value))
                                    ->map(fn ($value): string => is_array($value) ? implode(', ', $value) : (string) $value)
                                    ->all()),
                        ]),
                    Section::make('Internal notes')
                        ->icon('heroicon-o-pencil-square')
                        ->schema([
                            TextEntry::make('internal_notes')->hiddenLabel()->placeholder('No notes yet. Use “Add note” to record follow-ups.')->prose(),
                        ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Contact')
                        ->icon('heroicon-o-user')
                        ->schema([
                            TextEntry::make('full_name')->label('Name')->weight('medium'),
                            TextEntry::make('email')->copyable()->url(fn (ContactMessage $record): string => 'mailto:'.$record->email),
                            TextEntry::make('phone')->placeholder('—')->url(fn (ContactMessage $record): ?string => $record->phone ? 'tel:'.preg_replace('/[^0-9+]/', '', $record->phone) : null),
                            TextEntry::make('company')->placeholder('—'),
                        ]),
                    Section::make('Status')
                        ->icon('heroicon-o-flag')
                        ->schema([
                            TextEntry::make('status')->badge(),
                            TextEntry::make('handler.name')->label('Handled by')->placeholder('—'),
                            TextEntry::make('replied_at')->label('Replied')->dateTime('M j, Y g:i a')->placeholder('—'),
                            TextEntry::make('source')->formatStateUsing(fn (string $state): string => ucfirst($state).' form'),
                        ]),
                    Section::make('Technical')
                        ->collapsed()
                        ->collapsible()
                        ->schema([
                            TextEntry::make('ip_address')->label('IP address')->placeholder('—'),
                            TextEntry::make('user_agent')->label('Browser')->placeholder('—')->size('xs'),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
