<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Creates the initial Super Admin from SEED_ADMIN_* environment variables so
 * no credentials are ever committed.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SEED_ADMIN_EMAIL');
        $password = env('SEED_ADMIN_PASSWORD');

        if (blank($email) || blank($password)) {
            throw new RuntimeException('Set SEED_ADMIN_EMAIL and SEED_ADMIN_PASSWORD in .env before seeding.');
        }

        $user = User::query()->firstOrCreate(['email' => mb_strtolower($email)], [
            'name' => env('SEED_ADMIN_NAME', 'CSinc91 Administrator'),
            'password' => $password,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user->syncRoles([Role::SuperAdmin->value]);
    }
}
