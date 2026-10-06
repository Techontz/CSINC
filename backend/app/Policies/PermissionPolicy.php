<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

/**
 * Base policy mapping standard abilities onto a single "manage" permission.
 */
abstract class PermissionPolicy
{
    abstract protected function managePermission(): Permission;

    protected function deletePermission(): Permission
    {
        return $this->managePermission();
    }

    protected function allows(User $user, Permission $permission): bool
    {
        return $user->is_active && $user->can($permission->value);
    }

    public function viewAny(User $user): bool
    {
        return $this->allows($user, $this->managePermission());
    }

    public function view(User $user, mixed $model): bool
    {
        return $this->allows($user, $this->managePermission());
    }

    public function create(User $user): bool
    {
        return $this->allows($user, $this->managePermission());
    }

    public function update(User $user, mixed $model): bool
    {
        return $this->allows($user, $this->managePermission());
    }

    public function delete(User $user, mixed $model): bool
    {
        return $this->allows($user, $this->deletePermission());
    }

    public function deleteAny(User $user): bool
    {
        return $this->allows($user, $this->deletePermission());
    }

    public function restore(User $user, mixed $model): bool
    {
        return $this->allows($user, $this->deletePermission());
    }

    public function restoreAny(User $user): bool
    {
        return $this->allows($user, $this->deletePermission());
    }

    public function forceDelete(User $user, mixed $model): bool
    {
        return $this->allows($user, $this->deletePermission());
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->allows($user, $this->deletePermission());
    }

    public function reorder(User $user): bool
    {
        return $this->allows($user, $this->managePermission());
    }
}
