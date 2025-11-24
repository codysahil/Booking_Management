<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_employee_list()
    {
        $response = $this->get(route('admin.employees.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_employee()
    {
        Storage::fake('public');

        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $photo = UploadedFile::fake()->image('photo.jpg');
        $proof = UploadedFile::fake()->create('proof.pdf');

        $response = $this->post(route('admin.employees.store'), [
            'name' => 'John Doe',
            'role' => 'Warden',
            'phone' => '9876543210',
            'address' => '123 Main St',
            'branch_id' => $branch->id,
            'photo' => $photo,
            'proof' => $proof,
        ]);

        $response->assertRedirect(route('admin.employees.index'));
        $this->assertDatabaseHas('employees', ['name' => 'John Doe', 'role' => 'Warden']);

        Storage::disk('public')->assertExists('employees/photos/' . $photo->hashName());
        Storage::disk('public')->assertExists('employees/proofs/' . $proof->hashName());
    }

    public function test_admin_can_update_employee()
    {
        Storage::fake('public');

        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $employee = Employee::create([
            'employee_code' => 'EMP-001',
            'name' => 'Old Name',
            'role' => 'Cleaner',
            'phone' => '1111111111',
            'address' => 'Old Address',
            'branch_id' => $branch->id,
            'photo_path' => 'old.jpg',
            'proof_path' => 'old.pdf',
        ]);

        $response = $this->put(route('admin.employees.update', $employee), [
            'name' => 'New Name',
            'role' => 'Manager',
            'phone' => '2222222222',
            'address' => 'New Address',
            'branch_id' => $branch->id,
        ]);

        $response->assertRedirect(route('admin.employees.index'));
        $this->assertDatabaseHas('employees', ['name' => 'New Name', 'role' => 'Manager']);
    }

    public function test_admin_can_delete_employee()
    {
        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $employee = Employee::create([
            'employee_code' => 'EMP-001',
            'name' => 'To Delete',
            'role' => 'Cleaner',
            'phone' => '1111111111',
            'address' => 'Address',
            'branch_id' => $branch->id,
        ]);

        $response = $this->delete(route('admin.employees.destroy', $employee));

        $response->assertRedirect(route('admin.employees.index'));
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }
}
