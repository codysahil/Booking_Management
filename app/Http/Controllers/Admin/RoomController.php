<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Branch;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with(['branch', 'beds'])->paginate(15);
        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('admin.rooms.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'room_number' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'type' => 'required|in:AC,Non-AC',
            'gender_allowed' => 'required|in:Male,Female,Any',
        ]);

        Room::create($request->all());

        return redirect()->route('admin.rooms.index')->with('success', 'Room created successfully.');
    }

    public function edit(Room $room)
    {
        $branches = Branch::all();
        return view('admin.rooms.edit', compact('room', 'branches'));
    }

    public function update(Request $request, Room $room)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'room_number' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'type' => 'required|in:AC,Non-AC',
            'gender_allowed' => 'required|in:Male,Female,Any',
        ]);

        $room->update($request->all());

        return redirect()->route('admin.rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('admin.rooms.index')->with('success', 'Room deleted successfully.');
    }
}
