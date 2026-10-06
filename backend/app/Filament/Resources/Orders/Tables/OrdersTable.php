<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('items'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->searchable()->weight(FontWeight::Medium)->fontFamily('mono'),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(['customer_name', 'customer_email'])
                    ->description(fn (Order $record): string => $record->customer_email),
                TextColumn::make('items_count')->label('Items')->badge()->color('gray'),
                TextColumn::make('total_cents')->label('Total')->money(fn (Order $record): string => $record->currency, divideBy: 100)->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->label('Placed')->dateTime('M j, Y g:i a')->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options(OrderStatus::class)->default(OrderStatus::Paid->value)])
            ->recordActions([ViewAction::make()->iconButton()])
            ->emptyStateIcon('heroicon-o-receipt-percent')
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Orders placed through the website checkout will appear here.');
    }
}
