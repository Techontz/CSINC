<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\Role;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $role = $data['role'];
        unset($data['role']);

        abort_if($role === Role::SuperAdmin->value && ! Auth::user()->isSuperAdmin(), 403);

        $user = static::getModel()::query()->create([...$data, 'email_verified_at' => now()]);
        $user->syncRoles([$role]);

        return $user;
    }
}
