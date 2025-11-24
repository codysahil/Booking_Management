@extends('layouts.admin')

@section('header', 'Edit Room')

@section('content')
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
        <form action="{{ route('admin.rooms.update', $room) }}" method="POST">
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

            <div class="flex justify-end">
                <a href="{{ route('admin.rooms.index') }}"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded mr-2 hover:bg-gray-300">Cancel</a>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Update
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
@endsection