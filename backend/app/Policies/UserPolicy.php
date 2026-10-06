<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class UserPolicy extends PermissionPolicy
{
    protected function managePermission(): Permission
    {
        return Permission::ManageUsers;
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->isNot($model) && parent::delete($user, $model);
    }
}
