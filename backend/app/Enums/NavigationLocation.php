<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NavigationLocation: string implements HasLabel
{
    case Header = 'header';
    case Footer = 'footer';
    case Legal = 'legal';

    public function getLabel(): string
    {
        return match ($this) {
            self::Header => 'Main menu',
            self::Footer => 'Footer menu',
            self::Legal => 'Legal links',
        };
    }
}
