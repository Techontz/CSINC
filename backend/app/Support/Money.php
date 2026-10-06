<?php

namespace App\Support;

use NumberFormatter;

class Money
{
    public static function format(?int $cents, string $currency = 'USD'): ?string
    {
        if ($cents === null) {
            return null;
        }

        $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($cents / 100, $currency) ?: sprintf('%s %.2f', $currency, $cents / 100);
    }
}
