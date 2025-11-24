<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Branch;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Customer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_create_customer_page()
    {
        $response = $this->get(route('admin.customers.create'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_customer_with_bed_assignment()
    {
        Storage::fake('public');

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

        $photo = UploadedFile::fake()->image('photo.jpg');
        $idProof = UploadedFile::fake()->create('id_proof.pdf');

        $response = $this->post(route('admin.customers.store'), [
            'name' => 'Jane Doe',
            'phone' => '9876543210',
            'email' => 'jane@example.com',
            'dob' => '2000-01-01',
            'address' => '123 Main St',
            'guardian_phone' => '1234567890',
            'photo' => $photo,
            'id_proof' => $idProof,
            'bed_id' => $bed->id,
            'joining_date' => '2023-11-01',
        ]);

        $response->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseHas('customers', ['email' => 'jane@example.com']);
        $this->assertDatabaseHas('beds', ['id' => $bed->id, 'status' => 'occupied']);
        $this->assertDatabaseHas('bookings', ['bed_id' => $bed->id, 'status' => 'active']);

        // Assert files were stored
        Storage::disk('public')->assertExists('customers/photos/' . $photo->hashName());
        Storage::disk('public')->assertExists('customers/proofs/' . $idProof->hashName());
    }
}
