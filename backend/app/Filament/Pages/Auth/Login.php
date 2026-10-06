<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getHeading(): string|Htmlable|null
    {
        return 'Welcome back';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Sign in to manage the CSinc91 website, products and inquiries.';
    }
}
