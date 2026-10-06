<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class BookPolicy extends PermissionPolicy
{
    protected function managePermission(): Permission
    {
        return Permission::ManageBooks;
    }

    protected function deletePermission(): Permission
    {
        return Permission::DeleteBooks;
    }

    /**
     * Publishing, unpublishing and featuring change what the public sees.
     */
    public function publish(User $user): bool
    {
        return $this->allows($user, Permission::PublishBooks);
    }
}
