<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/products')->assertRedirect('/admin/login');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Welcome back');
    }

    public function test_staff_roles_can_open_the_dashboard(): void
    {
        foreach (Role::cases() as $role) {
            $this->actingAs($this->userWithRole($role))->get('/admin')->assertOk();
        }
    }

    public function test_users_without_a_role_cannot_access_the_panel(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_deactivated_staff_cannot_access_the_panel(): void
    {
        $user = $this->userWithRole(Role::Admin, ['is_active' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_every_admin_screen_renders_for_super_admin(): void
    {
        $this->actingAs($this->userWithRole(Role::SuperAdmin));

        foreach ([
            '/admin/products', '/admin/products/create', '/admin/product-categories', '/admin/product-tags',
            '/admin/orders', '/admin/contact-messages', '/admin/pages', '/admin/pages/create', '/admin/services',
            '/admin/navigation', '/admin/media', '/admin/users', '/admin/settings', '/admin/activity-logs', '/admin/profile',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_editor_cannot_reach_restricted_screens(): void
    {
        $this->actingAs($this->userWithRole(Role::Editor));

        $this->get('/admin/products')->assertOk();
        $this->get('/admin/pages')->assertOk();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/orders')->assertForbidden();
        $this->get('/admin/navigation')->assertForbidden();
        $this->get('/admin/activity-logs')->assertForbidden();
    }

    public function test_admin_cannot_manage_team_members(): void
    {
        $this->actingAs($this->userWithRole(Role::Admin));

        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/settings')->assertOk();
    }
}
