<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\Role;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * @property User $record
 */
class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role'] = $this->record->roles->first()?->name;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var User $record */
        $role = $data['role'];
        unset($data['role']);

        abort_if($role === Role::SuperAdmin->value && ! Auth::user()->isSuperAdmin(), 403);

        $isLastSuperAdmin = $record->isSuperAdmin()
            && User::role(Role::SuperAdmin->value)->count() === 1
            && $role !== Role::SuperAdmin->value;

        if ($isLastSuperAdmin) {
            throw ValidationException::withMessages(['data.role' => 'At least one Super Admin is required.']);
        }

        if ($record->is(Auth::user())) {
            unset($data['is_active']);
        }

        $record->update($data);
        $record->syncRoles([$role]);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
