@extends('layouts.admin')

@section('header', 'Add New Room')

@section('content')
    <div class="max-w-2xl mx-auto">
        <form action="{{ route('admin.rooms.store') }}" method="POST" enctype="multipart/form-data"
            class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <x-form.card title="Room Details" description="Which branch this room belongs to and its basic setup">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                </x-slot:icon>

                <div class="space-y-5">
                    <x-form.select name="branch_id" label="Branch" required>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-form.select>

                    <x-form.input name="room_number" label="Room Number" required placeholder="e.g. 101" />

                    <div class="grid grid-cols-2 gap-4">
                        <x-form.input name="capacity" label="Capacity" type="number" min="1" required />
                        <x-form.select name="type" label="Type" required>
                            <option value="Non-AC">Non-AC</option>
                            <option value="AC">AC</option>
                        </x-form.select>
                    </div>

                    <x-form.select name="gender_allowed" label="Gender Allowed" required>
                        <option value="Any">Any</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </x-form.select>
                </div>
            </x-form.card>

            <x-form.card title="Room Images" description="Up to 5 images, max 1MB each" :delay="80">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </x-slot:icon>

                <label for="images" class="group flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 px-4 py-8 text-center transition-all duration-200 hover:border-teal-300 hover:bg-teal-50/30">
                    <svg class="h-8 w-8 text-gray-400 transition-transform duration-200 group-hover:scale-110 group-hover:text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3"></path>
                    </svg>
                    <span class="text-sm font-medium text-teal-600">Click to upload <span class="text-gray-500 font-normal">or drag and drop</span></span>
                    <input type="file" name="images[]" id="images" accept="image/*" multiple class="sr-only">
                </label>
                <p class="mt-1.5 text-xs text-gray-500">Select multiple images at once (hold Ctrl/Cmd) or add one by one.</p>

                <div id="file-count" class="mt-3 text-sm text-teal-600 font-medium hidden"></div>
                <div id="error-message" class="form-field-error mt-3 text-sm text-rose-600 font-medium hidden"></div>
                <div id="image-preview" class="mt-4 grid grid-cols-3 gap-3 hidden"></div>
            </x-form.card>

            <div class="flex justify-end gap-3">
                <x-form.link-button :href="route('admin.rooms.index')">Cancel</x-form.link-button>
                <x-form.button label="Save Room" loading-label="Saving…" />
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('images');
            const preview = document.getElementById('image-preview');
            const fileCount = document.getElementById('file-count');
            const errorMessage = document.getElementById('error-message');
            let accumulatedFiles = [];

            const MAX_FILE_SIZE = 1 * 1024 * 1024; // 1MB
            const MAX_FILES = 5;

            if (!imageInput) return;

            imageInput.addEventListener('change', function(e) {
                const newFiles = Array.from(e.target.files);
                errorMessage.classList.add('hidden');

                for (let file of newFiles) {
                    if (file.size > MAX_FILE_SIZE) {
                        errorMessage.textContent = `${file.name} is too large (${(file.size / 1024 / 1024).toFixed(2)}MB). Max 1MB per image.`;
                        errorMessage.classList.remove('hidden');
                        return;
                    }
                }

                newFiles.forEach(file => {
                    if (!accumulatedFiles.some(f => f.name === file.name && f.size === file.size)) {
                        if (accumulatedFiles.length < MAX_FILES) {
                            accumulatedFiles.push(file);
                        }
                    }
                });

                if (accumulatedFiles.length >= MAX_FILES) {
                    errorMessage.textContent = `Maximum ${MAX_FILES} images allowed.`;
                    errorMessage.classList.remove('hidden');
                }

                const dt = new DataTransfer();
                accumulatedFiles.forEach(file => dt.items.add(file));
                imageInput.files = dt.files;

                renderPreviews();
            });

            function renderPreviews() {
                preview.innerHTML = '';

                if (accumulatedFiles.length > 0) {
                    fileCount.textContent = `${accumulatedFiles.length} image${accumulatedFiles.length > 1 ? 's' : ''} selected`;
                    fileCount.classList.remove('hidden');
                    preview.classList.remove('hidden');

                    accumulatedFiles.forEach((file, index) => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                const div = document.createElement('div');
                                div.className = 'relative group animate-[form-fade-up_0.3s_ease-out_both]';
                                div.innerHTML = `
                                    <img src="${event.target.result}" class="w-full h-24 object-cover rounded-xl border-2 border-teal-200 shadow-sm transition-transform duration-200 group-hover:scale-[1.03]">
                                    <span class="absolute top-1.5 left-1.5 bg-teal-600 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow">#${index + 1}</span>
                                    <button type="button" onclick="removeNewImage(${index})" class="absolute top-1.5 right-1.5 bg-rose-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-150 hover:scale-110">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                    <span class="absolute bottom-1.5 left-1.5 right-1.5 bg-black/60 text-white text-xs px-1.5 py-0.5 rounded-md truncate">${file.name}</span>
                                `;
                                preview.appendChild(div);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                } else {
                    preview.classList.add('hidden');
                    fileCount.classList.add('hidden');
                }
            }

            window.removeNewImage = function(index) {
                accumulatedFiles.splice(index, 1);
                const dt = new DataTransfer();
                accumulatedFiles.forEach(file => dt.items.add(file));
                imageInput.files = dt.files;
                renderPreviews();
            };
        });
    </script>
@endsection
