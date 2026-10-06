<?php

namespace App\Filament\Support;

use Filament\Forms\Components\TextInput;

/**
 * Edits an integer cents column as a decimal currency amount.
 */
class MoneyInput
{
    public static function make(string $name): TextInput
    {
        return TextInput::make($name)
            ->numeric()
            ->minValue(0)
            ->maxValue(1_000_000)
            ->step('0.01')
            ->prefix('$')
            ->formatStateUsing(fn ($state): ?string => $state === null ? null : number_format(((int) $state) / 100, 2, '.', ''))
            ->dehydrateStateUsing(fn ($state): ?int => ($state === null || $state === '') ? null : (int) round(((float) $state) * 100));
    }
}
