<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'images.*' => 'nullable|image|max:2048',
        ]);

        $room = Room::create($request->except('images'));

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('rooms', 'public');
                $room->images()->create([
                    'image_path' => $path,
                    'order' => $index + 1,
                ]);
            }
        }

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
            'images.*' => 'nullable|image|max:2048',
        ]);

        $room->update($request->except('images'));

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            $order = $room->images()->max('order') ?? 0;
            foreach ($request->file('images') as $image) {
                $path = $image->store('rooms', 'public');
                $room->images()->create([
                    'image_path' => $path,
                    'order' => ++$order,
                ]);
            }
        }

        return redirect()->route('admin.rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('admin.rooms.index')->with('success', 'Room deleted successfully.');
    }

    public function destroyImage(Room $room, RoomImage $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();
        return back()->with('success', 'Image deleted successfully.');
    }
}
