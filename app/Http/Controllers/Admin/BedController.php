<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Room;
use Illuminate\Http\Request;

class BedController extends Controller
{
    public function store(Request $request, Room $room)
    {
        $request->validate([
            'bed_number' => 'required|string|max:255',
            'monthly_rent' => 'required|numeric|min:0',
        ]);

        // Check if room has reached capacity
        $currentBedCount = $room->beds()->count();
        if ($currentBedCount >= $room->capacity) {
            return back()->withErrors(['error' => "Cannot add more beds. Room capacity is {$room->capacity} and currently has {$currentBedCount} beds."]);
        }

        $room->beds()->create([
            'bed_number' => $request->bed_number,
            'monthly_rent' => $request->monthly_rent,
            'status' => 'vacant',
        ]);

        return back()->with('success', 'Bed added successfully.');
    }

    public function destroy(Bed $bed)
    {
        $bed->delete();
        return back()->with('success', 'Bed deleted successfully.');
    }

    public function updateStatus(Request $request, Bed $bed)
    {
        $request->validate([
            'status' => 'required|in:vacant,occupied,reserved,maintenance',
        ]);

        $bed->update(['status' => $request->status]);

        return back()->with('success', 'Bed status updated successfully.');
    }
}
