<?php

namespace App\Policies;

use App\Enums\Permission;

class ServicePolicy extends PermissionPolicy
{
    protected function managePermission(): Permission
    {
        return Permission::ManageServices;
    }
}
