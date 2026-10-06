<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use App\Models\User;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Profile')
                    ->icon('heroicon-o-user')
                    ->columnSpan(['lg' => 2])
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(190),
                        TextInput::make('email')->email()->required()->maxLength(190)->unique(User::class, 'email', ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Leave blank to keep the current password.' : null),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->revealable()
                            ->same('password')
                            ->requiredWith('password')
                            ->dehydrated(false),
                    ]),
                Section::make('Access')
                    ->icon('heroicon-o-shield-check')
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Radio::make('role')
                            ->options(fn (): array => collect(Role::cases())
                                ->filter(fn (Role $role): bool => $role !== Role::SuperAdmin || Auth::user()->isSuperAdmin())
                                ->mapWithKeys(fn (Role $role): array => [$role->value => $role->getLabel()])
                                ->all())
                            ->descriptions([
                                Role::SuperAdmin->value => 'Full access, including team management.',
                                Role::Admin->value => 'Everything except managing team members.',
                                Role::Editor->value => 'Drafts products and edits content. Cannot publish, delete or change settings.',
                            ])
                            ->required()
                            ->default(Role::Editor->value),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive members cannot sign in.')
                            ->disabled(fn (?User $record): bool => $record?->is(Auth::user()) ?? false),
                    ]),
            ]);
    }
}
