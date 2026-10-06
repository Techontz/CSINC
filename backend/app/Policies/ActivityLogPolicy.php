<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class ActivityLogPolicy extends PermissionPolicy
{
    protected function managePermission(): Permission
    {
        return Permission::ViewActivity;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, mixed $model): bool
    {
        return false;
    }

    public function delete(User $user, mixed $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
