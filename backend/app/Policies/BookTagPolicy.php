<?php

namespace App\Policies;

use App\Enums\Permission;

class BookTagPolicy extends PermissionPolicy
{
    protected function managePermission(): Permission
    {
        return Permission::ManageBooks;
    }
}
