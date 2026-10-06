<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Items')
                        ->icon('heroicon-o-book-open')
                        ->schema([
                            RepeatableEntry::make('items')
                                ->hiddenLabel()
                                ->columns(4)
                                ->schema([
                                    TextEntry::make('title')->columnSpan(2)->weight('medium'),
                                    TextEntry::make('unit_price_cents')->label('Price')->money(fn (Order $record): string => $record->currency, divideBy: 100),
                                    TextEntry::make('download_count')->label('Downloads')->suffix('×'),
                                ]),
                            TextEntry::make('total_cents')
                                ->label('Order total')
                                ->money(fn (Order $record): string => $record->currency, divideBy: 100)
                                ->size('lg')
                                ->weight('semibold'),
                        ]),
                    Section::make('Internal notes')
                        ->schema([TextEntry::make('internal_notes')->hiddenLabel()->placeholder('No notes.')->prose()]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Customer')
                        ->icon('heroicon-o-user')
                        ->schema([
                            TextEntry::make('customer_name')->label('Name'),
                            TextEntry::make('customer_email')->label('Email')->copyable()->url(fn (Order $record): string => 'mailto:'.$record->customer_email),
                        ]),
                    Section::make('Payment')
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            TextEntry::make('status')->badge(),
                            TextEntry::make('created_at')->label('Placed')->dateTime('M j, Y g:i a'),
                            TextEntry::make('paid_at')->label('Paid')->dateTime('M j, Y g:i a')->placeholder('—'),
                            TextEntry::make('fulfilled_at')->label('Downloads emailed')->dateTime('M j, Y g:i a')->placeholder('—'),
                            TextEntry::make('terms_accepted_at')->label('Terms accepted')->dateTime('M j, Y g:i a')->placeholder('—'),
                            TextEntry::make('stripe_payment_intent')
                                ->label('Stripe payment')
                                ->placeholder('—')
                                ->copyable()
                                ->url(fn (Order $record): ?string => $record->stripe_payment_intent ? 'https://dashboard.stripe.com/payments/'.$record->stripe_payment_intent : null, shouldOpenInNewTab: true),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
