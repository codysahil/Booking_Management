<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAndTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_settings()
    {
        $owner = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $response = $this->actingAs($owner)->put(route('admin.settings.update'), [
            'hostel_name' => 'Sunrise Residency',
            'tagline' => 'A home away from home',
            'min_advance' => 3000,
            'rent_due_day' => 5,
            'late_fee' => 100,
            'notice_period_days' => 30,
            'online_payment_hold_minutes' => 30,
        ]);

        $response->assertRedirect();
        $this->assertSame('Sunrise Residency', Setting::get('hostel_name'));
    }

    public function test_owner_can_view_settings_and_team_pages()
    {
        $owner = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $this->actingAs($owner)->get(route('admin.settings.edit'))->assertStatus(200);
        $this->actingAs($owner)->get(route('admin.team.index'))->assertStatus(200);
    }

    public function test_manager_cannot_access_settings()
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER, 'is_active' => true]);

        $response = $this->actingAs($manager)->get(route('admin.settings.edit'));

        $response->assertForbidden();
    }

    public function test_manager_cannot_access_team_page()
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER, 'is_active' => true]);

        $response = $this->actingAs($manager)->get(route('admin.team.index'));

        $response->assertForbidden();
    }

    public function test_owner_can_add_a_manager_account()
    {
        $owner = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $response = $this->actingAs($owner)->post(route('admin.team.store'), [
            'name' => 'New Manager',
            'email' => 'manager@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_MANAGER,
        ]);

        $response->assertRedirect(route('admin.team.index'));
        $this->assertDatabaseHas('users', ['email' => 'manager@example.com', 'role' => User::ROLE_MANAGER]);
    }

    public function test_manager_can_sign_in_to_admin_panel()
    {
        $manager = User::factory()->create([
            'role' => User::ROLE_MANAGER,
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => $manager->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($manager);
    }
}
