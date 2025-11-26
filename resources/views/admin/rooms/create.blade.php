@extends('layouts.admin')

@section('header', 'Add New Room')

@section('content')
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
        <form action="{{ route('admin.rooms.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                <select name="branch_id" id="branch_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="room_number" class="block text-sm font-medium text-gray-700">Room Number</label>
                <input type="text" name="room_number" id="room_number"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                    <input type="number" name="capacity" id="capacity" min="1"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required>
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="type" id="type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required>
                        <option value="Non-AC">Non-AC</option>
                        <option value="AC">AC</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="gender_allowed" class="block text-sm font-medium text-gray-700">Gender Allowed</label>
                <select name="gender_allowed" id="gender_allowed"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required>
                    <option value="Any">Any</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Room Images</label>
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
                <button type="submit" id="submit-btn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Save
                    Room</button>
            </div>
        </form>
    </div>

    <script>
        console.log('Script loaded');
        
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
                    fileCount.textContent = `${accumulatedFiles.length} image${accumulatedFiles.length > 1 ? 's' : ''} selected`;
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
                                    <span class="absolute top-1 left-1 bg-indigo-600 text-white text-xs px-2 py-0.5 rounded-full shadow">#${index + 1}</span>
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