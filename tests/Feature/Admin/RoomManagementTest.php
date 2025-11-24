<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Branch;
use App\Models\Room;
use App\Models\Bed;

class RoomManagementTest extends TestCase
{
    use RefreshDatabase;

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
}
