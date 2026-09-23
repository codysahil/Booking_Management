<?php

namespace Tests\Feature\Admin;

use App\Models\Announcement;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

class AnnouncementManagementTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true]);
    }

    public function test_admin_can_publish_an_announcement()
    {
        $response = $this->post(route('admin.announcements.store'), [
            'title' => 'Water supply maintenance',
            'body' => 'Water will be off from 10am to 2pm on Saturday.',
            'is_pinned' => '1',
        ]);

        $response->assertRedirect(route('admin.announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'title' => 'Water supply maintenance',
            'is_pinned' => true,
        ]);
    }

    public function test_announcement_appears_on_customer_dashboard()
    {
        Announcement::create([
            'title' => 'Office closed on Sunday',
            'body' => 'The front office will be closed this Sunday.',
        ]);

        $customer = Customer::create([
            'customer_code' => 'SS-TEST-0001',
            'name' => 'Test Customer',
            'phone' => '9876543210',
            'password' => bcrypt('password'),
            'dob' => '2000-01-01',
            'address' => 'Test Address',
            'guardian_phone' => '9876543211',
        ]);

        $response = $this->actingAs($customer, 'customer')->get(route('customer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Office closed on Sunday');
    }

    public function test_admin_can_delete_an_announcement()
    {
        $announcement = Announcement::create(['title' => 'Old notice', 'body' => 'Old body']);

        $response = $this->delete(route('admin.announcements.destroy', $announcement));

        $response->assertRedirect();
        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }
}
