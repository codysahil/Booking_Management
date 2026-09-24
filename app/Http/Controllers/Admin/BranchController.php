<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('rooms')->paginate(10);
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'google_map_url' => 'nullable|url',
        ]);

        Branch::create($request->all());

        return redirect()->route('admin.branches.index')->with('success', 'Branch created successfully.');
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'google_map_url' => 'nullable|url',
        ]);

        $branch->update($request->all());

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $hasActiveBooking = Booking::where('status', Booking::STATUS_ACTIVE)
            ->whereHas('bed.room', fn ($q) => $q->where('branch_id', $branch->id))
            ->exists();

        if ($hasActiveBooking) {
            return back()->with('error', 'Cannot delete this branch — it still has residents checked in. Vacate them first.');
        }

        $branch->delete();
        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted successfully.');
    }
}
