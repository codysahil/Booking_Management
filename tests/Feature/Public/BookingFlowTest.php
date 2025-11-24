<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Branch;
use App\Models\Room;
use App\Models\Bed;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_view_home_page()
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('SereneStay');
    }

    public function test_public_can_view_branch_details()
    {
        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);

        $response = $this->get(route('booking.branch', $branch));
        $response->assertStatus(200);
        $response->assertSee('Test Branch');
    }

    public function test_public_can_view_room_details()
    {
        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $room = $branch->rooms()->create([
            'room_number' => '101',
            'capacity' => 2,
            'type' => 'AC',
            'gender_allowed' => 'Female',
        ]);

        $response = $this->get(route('booking.room', [$branch, $room]));
        $response->assertStatus(200);
        $response->assertSee('Room 101');
    }

    public function test_public_can_hold_bed()
    {
        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $room = $branch->rooms()->create([
            'room_number' => '101',
            'capacity' => 2,
            'type' => 'AC',
            'gender_allowed' => 'Female',
        ]);
        $bed = $room->beds()->create([
            'bed_number' => '101-A',
            'monthly_rent' => 5000,
            'status' => 'vacant',
        ]);

        $response = $this->post(route('booking.hold', $bed));
        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');
    }
}
