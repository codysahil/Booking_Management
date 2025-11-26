<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('branch')->latest()->get();
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('admin.employees.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|in:Manager,Warden,Cook,Security,Cleaner',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'photo' => 'nullable|image|max:2048',
            'proof' => 'nullable|file|max:2048',
        ]);

        $photoPath = $request->hasFile('photo') ? $request->file('photo')->store('employees/photos', 'public') : null;
        $proofPath = $request->hasFile('proof') ? $request->file('proof')->store('employees/proofs', 'public') : null;

        $employeeCode = 'EMP-' . date('Y') . '-' . str_pad(Employee::count() + 1, 3, '0', STR_PAD_LEFT);

        Employee::create([
            'employee_code' => $employeeCode,
            'name' => $request->name,
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'branch_id' => $request->branch_id,
            'photo_path' => $photoPath,
            'proof_path' => $proofPath,
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee added successfully.');
    }

    public function edit(Employee $employee)
    {
        $branches = Branch::all();
        return view('admin.employees.edit', compact('employee', 'branches'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|in:Manager,Warden,Cook,Security,Cleaner',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'photo' => 'nullable|image|max:2048',
            'proof' => 'nullable|file|max:2048',
        ]);

        $data = $request->only(['name', 'role', 'phone', 'address', 'branch_id']);

        if ($request->hasFile('photo')) {
            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('employees/photos', 'public');
        }

        if ($request->hasFile('proof')) {
            if ($employee->proof_path) {
                Storage::disk('public')->delete($employee->proof_path);
            }
            $data['proof_path'] = $request->file('proof')->store('employees/proofs', 'public');
        }

        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load('branch');
        return view('admin.employees.show', compact('employee'));
    }

    public function destroy(Employee $employee)
    {
        if ($employee->photo_path) {
            Storage::disk('public')->delete($employee->photo_path);
        }
        if ($employee->proof_path) {
            Storage::disk('public')->delete($employee->proof_path);
        }

        $employee->delete();
        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully.');
    }
}
