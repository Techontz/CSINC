<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class ContactMessagePolicy extends PermissionPolicy
{
    protected function managePermission(): Permission
    {
        return Permission::ManageMessages;
    }

    public function viewAny(User $user): bool
    {
        return $this->allows($user, Permission::ViewMessages) || parent::viewAny($user);
    }

    public function view(User $user, mixed $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return false;
    }
}
