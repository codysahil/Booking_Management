<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('branch')->orderByDesc('is_pinned')->latest()->paginate(20);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        $branches = Branch::all();

        return view('admin.announcements.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['created_by'] = Auth::id();

        Announcement::create($validated);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement published.');
    }

    public function edit(Announcement $announcement)
    {
        $branches = Branch::all();

        return view('admin.announcements.edit', compact('announcement', 'branches'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $announcement->update($this->validated($request));

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return back()->with('success', 'Announcement removed.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id',
            'is_pinned' => 'boolean',
            'expires_on' => 'nullable|date',
        ]);

        $validated['is_pinned'] = $request->boolean('is_pinned');

        return $validated;
    }
}
