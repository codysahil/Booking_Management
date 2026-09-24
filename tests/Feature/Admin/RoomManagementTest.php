<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Branch;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\User;
use Tests\Concerns\CreatesTenantContext;

class RoomManagementTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true]);
    }

    public function test_admin_can_create_branch()
    {
        $response = $this->post(route('admin.branches.store'), [
            'name' => 'Main Branch',
            'address' => '123 Main St',
            'google_map_url' => 'http://maps.google.com',
        ]);

        $response->assertRedirect(route('admin.branches.index'));
        $this->assertDatabaseHas('branches', ['name' => 'Main Branch']);
    }

    public function test_admin_can_create_room()
    {
        $branch = Branch::create(['name' => 'Branch 1', 'address' => 'Addr']);

        $response = $this->post(route('admin.rooms.store'), [
            'branch_id' => $branch->id,
            'room_number' => '101',
            'capacity' => 2,
            'type' => 'AC',
            'gender_allowed' => 'Male',
        ]);

        $response->assertRedirect(route('admin.rooms.index'));
        $this->assertDatabaseHas('rooms', ['room_number' => '101']);
    }

    public function test_admin_can_add_bed_to_room()
    {
        $branch = Branch::create(['name' => 'Branch 1', 'address' => 'Addr']);
        $room = Room::create([
            'branch_id' => $branch->id,
            'room_number' => '101',
            'capacity' => 2,
            'type' => 'AC',
            'gender_allowed' => 'Male',
        ]);

        $response = $this->post(route('admin.rooms.beds.store', $room), [
            'bed_number' => '101-A',
            'monthly_rent' => 5000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('beds', ['bed_number' => '101-A', 'room_id' => $room->id]);
    }

    public function test_admin_can_update_bed_status()
    {
        $branch = Branch::create(['name' => 'Branch 1', 'address' => 'Addr']);
        $room = Room::create([
            'branch_id' => $branch->id,
            'room_number' => '101',
            'capacity' => 2,
            'type' => 'AC',
            'gender_allowed' => 'Male',
        ]);
        $bed = $room->beds()->create([
            'bed_number' => '101-A',
            'monthly_rent' => 5000,
            'status' => 'vacant',
        ]);

        $response = $this->put(route('admin.beds.update-status', $bed), [
            'status' => 'occupied',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('beds', ['id' => $bed->id, 'status' => 'occupied']);
    }

    private function createOccupiedBed(): Bed
    {
        $branch = Branch::create(['name' => 'Branch 1', 'address' => 'Addr']);
        $room = Room::create([
            'branch_id' => $branch->id, 'room_number' => '101', 'capacity' => 2,
            'type' => 'AC', 'gender_allowed' => 'Male',
        ]);
        $bed = $room->beds()->create(['bed_number' => '101-A', 'monthly_rent' => 5000, 'status' => 'occupied']);
        $customer = Customer::create([
            'customer_code' => 'CUST-100', 'name' => 'Resident', 'phone' => '9998887770',
            'password' => bcrypt('password'), 'dob' => '2000-01-01', 'address' => 'Addr', 'guardian_phone' => '9998887771',
        ]);
        $customer->bookings()->create([
            'booking_reference' => 'BK-TEST0100', 'bed_id' => $bed->id, 'check_in_date' => now()->subMonth(),
            'status' => Booking::STATUS_ACTIVE, 'advance_paid' => 5000,
        ]);

        return $bed;
    }

    public function test_admin_cannot_delete_a_bed_with_an_active_resident()
    {
        $bed = $this->createOccupiedBed();

        $response = $this->delete(route('admin.beds.destroy', $bed));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('beds', ['id' => $bed->id]);
    }

    public function test_admin_cannot_delete_a_room_with_an_active_resident()
    {
        $bed = $this->createOccupiedBed();

        $response = $this->delete(route('admin.rooms.destroy', $bed->room));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('rooms', ['id' => $bed->room->id]);
    }

    public function test_admin_cannot_delete_a_branch_with_an_active_resident()
    {
        $bed = $this->createOccupiedBed();
        $branch = $bed->room->branch;

        $response = $this->delete(route('admin.branches.destroy', $branch));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('branches', ['id' => $branch->id]);
    }

    public function test_admin_can_delete_a_vacant_bed_room_and_branch()
    {
        $branch = Branch::create(['name' => 'Branch 1', 'address' => 'Addr']);
        $room = Room::create([
            'branch_id' => $branch->id, 'room_number' => '101', 'capacity' => 2,
            'type' => 'AC', 'gender_allowed' => 'Male',
        ]);
        $bed = $room->beds()->create(['bed_number' => '101-A', 'monthly_rent' => 5000, 'status' => 'vacant']);

        $this->delete(route('admin.beds.destroy', $bed))->assertRedirect();
        $this->assertDatabaseMissing('beds', ['id' => $bed->id]);

        $this->delete(route('admin.rooms.destroy', $room))->assertRedirect(route('admin.rooms.index'));
        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);

        $this->delete(route('admin.branches.destroy', $branch))->assertRedirect(route('admin.branches.index'));
        $this->assertDatabaseMissing('branches', ['id' => $branch->id]);
    }
}
