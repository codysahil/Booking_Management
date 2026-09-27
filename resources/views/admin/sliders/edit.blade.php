@extends('layouts.admin')

@section('header', 'Edit Slider Image')

@section('content')
    <div class="max-w-2xl mx-auto">
        <form action="{{ route('admin.sliders.update', $slider) }}" method="POST" enctype="multipart/form-data"
            x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <x-form.card title="Slider Image">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </x-slot:icon>

                <div class="space-y-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Current Image</p>
                        <img src="{{ $slider->image_url }}" alt="{{ $slider->title }}" class="w-full h-56 object-cover rounded-xl border-2 border-gray-200 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Replace Image (optional)</label>
                        <label for="image" class="group flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed {{ $errors->has('image') ? 'border-rose-300 bg-rose-50/30' : 'border-gray-200 hover:border-teal-300 hover:bg-teal-50/30' }} px-4 py-6 text-center transition-all duration-200">
                            <svg class="h-7 w-7 text-gray-400 transition-transform duration-200 group-hover:scale-110 group-hover:text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3"></path>
                            </svg>
                            <span class="text-sm font-medium text-teal-600">Click to upload <span class="text-gray-500 font-normal">or drag and drop</span></span>
                            <input type="file" name="image" id="image" accept="image/*" class="sr-only" onchange="previewImage(event)">
                        </label>
                        <p class="mt-1.5 text-xs text-gray-500">Leave empty to keep the current image.</p>
                        @error('image')
                            <p class="form-field-error mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror

                        <div id="imagePreview" class="mt-4 hidden">
                            <img id="preview" class="w-full h-56 object-cover rounded-xl border-2 border-teal-200 shadow-sm">
                        </div>
                    </div>

                    <x-form.input name="title" label="Title (optional)" :value="$slider->title" />
                    <x-form.textarea name="description" label="Description (optional)" :rows="3" :value="$slider->description" />
                    <x-form.input name="order" label="Display Order" type="number" min="0" :value="$slider->order" hint="Lower numbers appear first" />
                </div>
            </x-form.card>

            <div class="mt-6 flex justify-end gap-3">
                <x-form.link-button :href="route('admin.sliders.index')">Cancel</x-form.link-button>
                <x-form.button label="Update Slider Image" loading-label="Saving…" />
            </div>
        </form>
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
