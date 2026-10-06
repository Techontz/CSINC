<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\Role;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('roles'))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight(FontWeight::Medium)->description(fn (User $record): string => $record->email),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Role::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'primary',
                        'admin' => 'info',
                        default => 'gray',
                    }),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('last_login_at')->label('Last sign-in')->since()->placeholder('Never')->sortable(),
            ])
            ->recordActions([EditAction::make()->iconButton(), DeleteAction::make()->iconButton()]);
    }
}
