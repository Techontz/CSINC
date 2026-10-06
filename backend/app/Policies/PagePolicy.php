<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class PagePolicy extends PermissionPolicy
{
    protected function managePermission(): Permission
    {
        return Permission::ManagePages;
    }

    /**
     * System pages (home, legal, etc.) back fixed routes and cannot be removed.
     */
    public function delete(User $user, mixed $model): bool
    {
        return ! $model->is_system && parent::delete($user, $model);
    }

    public function forceDelete(User $user, mixed $model): bool
    {
        return ! $model->is_system && parent::forceDelete($user, $model);
    }
}
