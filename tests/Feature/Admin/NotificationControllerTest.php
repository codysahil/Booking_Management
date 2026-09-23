<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeNotification(User $admin): string
    {
        $admin->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\NewBookingReceived',
            'data' => ['message' => 'Test notification'],
        ]);

        return $admin->notifications()->first()->id;
    }

    public function test_mark_read_redirects_to_a_same_site_relative_url()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);
        $notificationId = $this->makeNotification($admin);

        $response = $this->actingAs($admin)->get(route('admin.notifications.read', $notificationId) . '?url=' . urlencode('/admin/bookings'));

        $response->assertRedirect('/admin/bookings');
        $this->assertNotNull($admin->notifications()->first()->read_at);
    }

    /** An attacker-controlled full URL (or protocol-relative //host) must never be followed. */
    public function test_mark_read_ignores_an_off_site_url_and_falls_back_to_back()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);
        $notificationId = $this->makeNotification($admin);

        $response = $this->actingAs($admin)
            ->from('/admin/dashboard')
            ->get(route('admin.notifications.read', $notificationId) . '?url=' . urlencode('https://evil.example'));

        $response->assertRedirect('/admin/dashboard');

        $response2 = $this->actingAs($admin)
            ->from('/admin/dashboard')
            ->get(route('admin.notifications.read', $notificationId) . '?url=' . urlencode('//evil.example'));

        $response2->assertRedirect('/admin/dashboard');
    }
}
