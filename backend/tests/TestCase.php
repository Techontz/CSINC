<?php

namespace Tests;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');
    }

    protected function seedRoles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function userWithRole(Role $role, array $attributes = []): User
    {
        $this->seedRoles();

        $user = User::factory()->create(['is_active' => true, ...$attributes]);
        $user->assignRole($role->value);

        return $user;
    }
}
