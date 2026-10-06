<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Editor = 'editor';

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Editor => 'Editor',
        };
    }

    /**
     * @return list<string>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdmin => Permission::values(),
            self::Admin => array_values(array_diff(Permission::values(), [Permission::ManageUsers->value])),
            self::Editor => [
                Permission::ManageBooks->value,
                Permission::ManagePages->value,
                Permission::ManageServices->value,
                Permission::ManageMedia->value,
                Permission::ViewMessages->value,
                Permission::ManageSeo->value,
            ],
        };
    }
}
