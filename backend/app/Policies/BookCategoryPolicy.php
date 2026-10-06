<?php

namespace App\Policies;

use App\Enums\Permission;

class BookCategoryPolicy extends PermissionPolicy
{
    protected function managePermission(): Permission
    {
        return Permission::ManageBooks;
    }

    protected function deletePermission(): Permission
    {
        return Permission::DeleteBooks;
    }
}
