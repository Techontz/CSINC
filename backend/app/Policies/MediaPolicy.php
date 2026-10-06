<?php

namespace App\Policies;

use App\Enums\Permission;

class MediaPolicy extends PermissionPolicy
{
    protected function managePermission(): Permission
    {
        return Permission::ManageMedia;
    }
}
