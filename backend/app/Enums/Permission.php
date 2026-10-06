<?php

namespace App\Enums;

enum Permission: string
{
    case ManageBooks = 'manage books';
    case PublishBooks = 'publish books';
    case DeleteBooks = 'delete books';
    case ManagePages = 'manage pages';
    case ManageServices = 'manage services';
    case ManageNavigation = 'manage navigation';
    case ManageMedia = 'manage media';
    case ViewMessages = 'view messages';
    case ManageMessages = 'manage messages';
    case ManageOrders = 'manage orders';
    case ManageSettings = 'manage settings';
    case ManageSeo = 'manage seo';
    case ManageUsers = 'manage users';
    case ViewActivity = 'view activity';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $permission): string => $permission->value, self::cases());
    }
}
