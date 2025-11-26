@extends('layouts.admin')

@section('header', 'Edit Room')

@section('content')
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
        <form action="{{ route('admin.rooms.update', $room) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                <select name="branch_id" id="branch_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $room->branch_id == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="room_number" class="block text-sm font-medium text-gray-700">Room Number</label>
                <input type="text" name="room_number" id="room_number" value="{{ $room->room_number }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                    <input type="number" name="capacity" id="capacity" min="1" value="{{ $room->capacity }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required>
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="type" id="type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required>
                        <option value="Non-AC" {{ $room->type == 'Non-AC' ? 'selected' : '' }}>Non-AC</option>
                        <option value="AC" {{ $room->type == 'AC' ? 'selected' : '' }}>AC</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="gender_allowed" class="block text-sm font-medium text-gray-700">Gender Allowed</label>
                <select name="gender_allowed" id="gender_allowed"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required>
                    <option value="Any" {{ $room->gender_allowed == 'Any' ? 'selected' : '' }}>Any</option>
                    <option value="Male" {{ $room->gender_allowed == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ $room->gender_allowed == 'Female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Room Images</label>
                
                @if($room->images->count() > 0)
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Current Images:</p>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach($room->images as $image)
                                <div class="relative group">
                                    <img src="{{ Storage::url($image->image_path) }}" alt="Room image" class="w-full h-24 object-cover rounded-lg border-2 border-gray-200">
                                    <button type="button" onclick="deleteImage({{ $room->id }}, {{ $image->id }})" class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <input type="file" name="images[]" id="images" accept="image/*" multiple
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-500 mt-1">Select multiple images at once (Hold Ctrl/Cmd) or add one by one. Max: 1MB per image, 5 images total</p>
                
                <div id="file-count" class="mt-2 text-sm text-indigo-600 font-medium hidden"></div>
                <div id="error-message" class="mt-2 text-sm text-red-600 font-medium hidden"></div>
                <div id="image-preview" class="mt-4 grid grid-cols-3 gap-3 hidden"></div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('admin.rooms.index') }}"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded mr-2 hover:bg-gray-300">Cancel</a>
                <button type="submit" id="submit-btn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700" onclick="console.log('Button clicked!')">Update
                    Room</button>
            </div>
        </form>
    </div>

    <!-- Bed Management Section -->
    <div class="max-w-4xl mx-auto mt-8 bg-white p-4 sm:p-6 rounded-lg shadow">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
            <h2 class="text-xl font-bold mb-2 sm:mb-0">Manage Beds</h2>
            <div class="text-sm">
                <span class="font-medium text-gray-700">Capacity: {{ $room->capacity }}</span>
                <span class="mx-2">|</span>
                <span class="font-medium {{ $room->beds->count() >= $room->capacity ? 'text-red-600' : 'text-green-600' }}">
                    Current Beds: {{ $room->beds->count() }}
                </span>
            </div>
        </div>

        @if($room->beds->count() >= $room->capacity)
            <div class="mb-4 bg-amber-50 border-2 border-amber-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-amber-800">Room capacity reached!</p>
                        <p class="text-xs text-amber-700 mt-1">This room has {{ $room->capacity }} capacity and already has {{ $room->beds->count() }} beds. Cannot add more beds.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Add Bed Form -->
        <form action="{{ route('admin.rooms.beds.store', $room) }}" method="POST" class="mb-6 bg-gray-50 p-4 rounded-lg">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="bed_number" class="block text-sm font-medium text-gray-700 mb-1">Bed Number</label>
                    <input type="text" name="bed_number" id="bed_number" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" 
                        placeholder="e.g., Bed 1" required>
                </div>
                <div>
                    <label for="monthly_rent" class="block text-sm font-medium text-gray-700 mb-1">Monthly Rent (₹)</label>
                    <input type="number" name="monthly_rent" id="monthly_rent" min="0" step="0.01" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" 
                        placeholder="3000" required>
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                        class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition text-sm font-medium"
                        {{ $room->beds->count() >= $room->capacity ? 'disabled' : '' }}>
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Bed
                    </button>
                </div>
            </div>
            @error('error')
                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </form>

        <!-- Beds List -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bed No</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rent</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($room->beds as $bed)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $bed->bed_number }}</td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹{{ number_format($bed->monthly_rent) }}</td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm">
                                <form action="{{ route('admin.beds.update-status', $bed) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" onchange="this.form.submit()" 
                                        class="text-xs sm:text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-xs sm:text-sm" 
                                        onclick="return confirm('Delete this bed?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                No beds added yet. Add beds using the form above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        console.log('Script loaded');
        
        // Function to delete existing images
        window.deleteImage = function(roomId, imageId) {
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
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            
            const imageInput = document.getElementById('images');
            const preview = document.getElementById('image-preview');
            const fileCount = document.getElementById('file-count');
            const errorMessage = document.getElementById('error-message');
            let accumulatedFiles = [];
            
            const MAX_FILE_SIZE = 1 * 1024 * 1024; // 1MB
            const MAX_FILES = 5;
            
            if (!imageInput) {
                console.error('Image input not found');
                return;
            }
            
            imageInput.addEventListener('change', function(e) {
                const newFiles = Array.from(e.target.files);
                console.log('New files selected:', newFiles.length);
                errorMessage.classList.add('hidden');
                
                // Validate files
                for (let file of newFiles) {
                    if (file.size > MAX_FILE_SIZE) {
                        errorMessage.textContent = `${file.name} is too large (${(file.size / 1024 / 1024).toFixed(2)}MB). Max 1MB per image.`;
                        errorMessage.classList.remove('hidden');
                        return;
                    }
                }
                
                // Add new files to accumulated list (avoid duplicates)
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
                
                console.log('Total accumulated:', accumulatedFiles.length);
                
                // Update file input with all accumulated files
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
                            reader.onload = function(event) {
                                const div = document.createElement('div');
                                div.className = 'relative group';
                                div.innerHTML = `
                                    <img src="${event.target.result}" class="w-full h-24 object-cover rounded-lg border-2 border-indigo-300">
                                    <span class="absolute top-1 left-1 bg-indigo-600 text-white text-xs px-2 py-0.5 rounded-full shadow">New #${index + 1}</span>
                                    <button type="button" onclick="removeNewImage(${index})" class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                    <span class="absolute bottom-1 left-1 right-1 bg-black bg-opacity-50 text-white text-xs px-1 py-0.5 rounded truncate">${file.name}</span>
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