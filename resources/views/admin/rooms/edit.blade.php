@extends('layouts.admin')

@section('header', 'Edit Room')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <form action="{{ route('admin.rooms.update', $room) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <x-form.card title="Room Details">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                </x-slot:icon>

                <div class="space-y-5">
                    <x-form.select name="branch_id" label="Branch" required>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $room->branch_id == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </x-form.select>

                    <x-form.input name="room_number" label="Room Number" required :value="$room->room_number" />

                    <div class="grid grid-cols-2 gap-4">
                        <x-form.input name="capacity" label="Capacity" type="number" min="1" required :value="$room->capacity" />
                        <x-form.select name="type" label="Type" required>
                            <option value="Non-AC" {{ $room->type == 'Non-AC' ? 'selected' : '' }}>Non-AC</option>
                            <option value="AC" {{ $room->type == 'AC' ? 'selected' : '' }}>AC</option>
                        </x-form.select>
                    </div>

                    <x-form.select name="gender_allowed" label="Gender Allowed" required>
                        <option value="Any" {{ $room->gender_allowed == 'Any' ? 'selected' : '' }}>Any</option>
                        <option value="Male" {{ $room->gender_allowed == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ $room->gender_allowed == 'Female' ? 'selected' : '' }}>Female</option>
                    </x-form.select>
                </div>
            </x-form.card>

            <x-form.card title="Room Images" description="Up to 5 new images, max 1MB each" :delay="80">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </x-slot:icon>

                @if($room->images->count() > 0)
                    <div class="mb-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Current Images</p>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach($room->images as $image)
                                <div class="relative group">
                                    <img src="{{ $image->safe_url }}" alt="Room image"
                                        class="w-full h-24 object-cover rounded-xl border-2 border-gray-200 shadow-sm transition-transform duration-200 group-hover:scale-[1.03]">
                                    <button type="button" onclick="deleteImage({{ $room->id }}, {{ $image->id }})"
                                        class="absolute top-1.5 right-1.5 bg-rose-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-150 hover:scale-110">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <label for="images" class="group flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 px-4 py-8 text-center transition-all duration-200 hover:border-teal-300 hover:bg-teal-50/30">
                    <svg class="h-8 w-8 text-gray-400 transition-transform duration-200 group-hover:scale-110 group-hover:text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3"></path>
                    </svg>
                    <span class="text-sm font-medium text-teal-600">Click to upload <span class="text-gray-500 font-normal">or drag and drop</span></span>
                    <input type="file" name="images[]" id="images" accept="image/*" multiple class="sr-only">
                </label>

                <div id="file-count" class="mt-3 text-sm text-teal-600 font-medium hidden"></div>
                <div id="error-message" class="form-field-error mt-3 text-sm text-rose-600 font-medium hidden"></div>
                <div id="image-preview" class="mt-4 grid grid-cols-3 gap-3 hidden"></div>
            </x-form.card>

            <div class="flex justify-end gap-3">
                <x-form.link-button :href="route('admin.rooms.index')">Cancel</x-form.link-button>
                <x-form.button label="Update Room" loading-label="Saving…" />
            </div>
        </form>

        <div class="form-card-enter rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8" style="animation-delay: 160ms">
            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="font-display text-lg font-bold text-gray-900">Manage Beds</h2>
                <div class="text-sm">
                    <span class="font-medium text-gray-500">Capacity: {{ $room->capacity }}</span>
                    <span class="mx-2 text-gray-300">·</span>
                    <span class="font-semibold {{ $room->beds->count() >= $room->capacity ? 'text-rose-600' : 'text-teal-600' }}">
                        {{ $room->beds->count() }} bed{{ $room->beds->count() === 1 ? '' : 's' }}
                    </span>
                </div>
            </div>

            @if($room->beds->count() >= $room->capacity)
                <div class="mb-5 flex items-start gap-3 rounded-xl border-2 border-amber-200 bg-amber-50 p-4">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Room capacity reached</p>
                        <p class="text-xs text-amber-700 mt-0.5">This room already has {{ $room->beds->count() }} of {{ $room->capacity }} beds. Cannot add more.</p>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.rooms.beds.store', $room) }}" method="POST"
                class="mb-6 rounded-xl bg-gray-50 p-4" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:items-end">
                    <x-form.input name="bed_number" label="Bed Number" required placeholder="e.g. Bed 1" />
                    <x-form.input name="monthly_rent" label="Monthly Rent (₹)" type="number" min="0" step="0.01" required placeholder="3000" />
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-teal-500 to-cyan-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                        :disabled="submitting"
                        {{ $room->beds->count() >= $room->capacity ? 'disabled' : '' }}>
                        <svg x-show="submitting" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Bed
                    </button>
                </div>
                @error('error')
                    <p class="form-field-error text-rose-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </form>

            <div class="overflow-x-auto -mx-6 sm:mx-0">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Bed No</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Rent</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($room->beds as $bed)
                            <tr class="transition-colors hover:bg-gray-50/80">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $bed->bed_number }}</td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹{{ number_format($bed->monthly_rent) }}</td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm">
                                    <form action="{{ route('admin.beds.update-status', $bed) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" onchange="this.form.submit()"
                                            class="rounded-lg border-2 border-gray-200 text-xs sm:text-sm shadow-sm transition-colors focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10">
                                            <option value="vacant" {{ $bed->status == 'vacant' ? 'selected' : '' }}>Vacant</option>
                                            <option value="occupied" {{ $bed->status == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                            <option value="reserved" {{ $bed->status == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                            <option value="maintenance" {{ $bed->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form action="{{ route('admin.beds.destroy', $bed) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs sm:text-sm font-semibold transition-colors"
                                            onclick="return confirm('Delete this bed?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-400">
                                    No beds added yet. Add beds using the form above.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Function to delete existing images
        window.deleteImage = function (roomId, imageId) {
            if (!confirm('Delete this image?')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/rooms/${roomId}/images/${imageId}`;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';

            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        };

        document.addEventListener('DOMContentLoaded', function () {
            const imageInput = document.getElementById('images');
            const preview = document.getElementById('image-preview');
            const fileCount = document.getElementById('file-count');
            const errorMessage = document.getElementById('error-message');
            let accumulatedFiles = [];

            const MAX_FILE_SIZE = 1 * 1024 * 1024; // 1MB
            const MAX_FILES = 5;

            if (!imageInput) return;

            imageInput.addEventListener('change', function (e) {
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
                    fileCount.textContent = `${accumulatedFiles.length} new image${accumulatedFiles.length > 1 ? 's' : ''} selected`;
                    fileCount.classList.remove('hidden');
                    preview.classList.remove('hidden');

                    accumulatedFiles.forEach((file, index) => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function (event) {
                                const div = document.createElement('div');
                                div.className = 'relative group animate-[form-fade-up_0.3s_ease-out_both]';
                                div.innerHTML = `
                                        <img src="${event.target.result}" class="w-full h-24 object-cover rounded-xl border-2 border-teal-200 shadow-sm transition-transform duration-200 group-hover:scale-[1.03]">
                                        <span class="absolute top-1.5 left-1.5 bg-teal-600 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow">New #${index + 1}</span>
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

            window.removeNewImage = function (index) {
                accumulatedFiles.splice(index, 1);
                const dt = new DataTransfer();
                accumulatedFiles.forEach(file => dt.items.add(file));
                imageInput.files = dt.files;
                renderPreviews();
            };
        });
    </script>
@endsection
