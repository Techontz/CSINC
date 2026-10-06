<?php

namespace App\Policies;

use App\Enums\Permission;

class NavigationItemPolicy extends PermissionPolicy
{
    protected function managePermission(): Permission
    {
        return Permission::ManageNavigation;
    }
}
