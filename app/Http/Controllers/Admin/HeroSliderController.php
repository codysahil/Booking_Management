<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeroSliderController extends Controller
{
    public function index()
    {
        $sliders = HeroSlider::orderBy('order')->get();
        return view('admin.sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('admin.sliders.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $imagePath = $request->file('image')->store('sliders');

        HeroSlider::create([
            'image_path' => $imagePath,
            'title' => $request->title,
            'description' => $request->description,
            'order' => $request->order ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('admin.sliders.index')
            ->with('success', 'Slider image added successfully');
    }

    public function edit(HeroSlider $slider)
    {
        return view('admin.sliders.edit', compact('slider'));
    }

    public function update(Request $request, HeroSlider $slider)
    {
        $request->validate([
            'image' => 'nullable|image|max:2048',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'order' => $request->order ?? 0,
        ];

        if ($request->hasFile('image')) {
            // Delete old image
            if ($slider->image_path) {
                Storage::delete($slider->image_path);
            }
            $data['image_path'] = $request->file('image')->store('sliders');
        }

        $slider->update($data);

        return redirect()->route('admin.sliders.index')
            ->with('success', 'Slider image updated successfully');
    }

    public function destroy(HeroSlider $slider)
    {
        if ($slider->image_path) {
            Storage::delete($slider->image_path);
        }

        $slider->delete();

        return redirect()->route('admin.sliders.index')
            ->with('success', 'Slider image deleted successfully');
    }

    public function toggleStatus(HeroSlider $slider)
    {
        $slider->update(['is_active' => !$slider->is_active]);

        return back()->with('success', 'Slider status updated');
    }
}
