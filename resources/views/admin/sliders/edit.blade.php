@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Edit Slider Image</h1>
            <a href="{{ route('admin.sliders.index') }}" class="text-gray-600 hover:text-gray-800">
                ← Back to Sliders
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('admin.sliders.update', $slider) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Current Image</label>
                    <img src="{{ Storage::url($slider->image_path) }}" alt="{{ $slider->title }}" class="w-full h-64 object-cover rounded-lg border-2 border-gray-200 mb-2">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Replace Image (Optional)</label>
                    <input type="file" name="image" accept="image/*"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('image') border-red-500 @enderror"
                        onchange="previewImage(event)">
                    @error('image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 mt-1">Leave empty to keep current image</p>
                    
                    <div id="imagePreview" class="mt-4 hidden">
                        <img id="preview" class="w-full h-64 object-cover rounded-lg border-2 border-gray-200">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Title (Optional)</label>
                    <input type="text" name="title" value="{{ old('title', $slider->title) }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Description (Optional)</label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $slider->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Display Order</label>
                    <input type="number" name="order" value="{{ old('order', $slider->order) }}" min="0"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('order') border-red-500 @enderror">
                    @error('order')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 mt-1">Lower numbers appear first</p>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                        Update Slider Image
                    </button>
                    <a href="{{ route('admin.sliders.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 rounded-lg text-center transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
