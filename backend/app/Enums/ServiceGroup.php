<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ServiceGroup: string implements HasLabel
{
    case Business = 'business';
    case Healthcare = 'healthcare';
    case Digital = 'digital';

    public function getLabel(): string
    {
        return match ($this) {
            self::Business => 'Business services',
            self::Healthcare => 'Healthcare & housing',
            self::Digital => 'Digital & automation',
        };
    }
}
