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
    <div class="max-w-4xl mx-auto mt-8 bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">Manage Beds</h2>

        <!-- Add Bed Form -->
        <form action="{{ route('admin.rooms.beds.store', $room) }}" method="POST" class="mb-6 bg-gray-50 p-4 rounded">
            @csrf
            <div class="grid grid-cols-3 gap-4 items-end">
                <div>
                    <label for="bed_number" class="block text-sm font-medium text-gray-700">Bed Number</label>
                    <input type="text" name="bed_number" id="bed_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="monthly_rent" class="block text-sm font-medium text-gray-700">Monthly Rent</label>
                    <input type="number" name="monthly_rent" id="monthly_rent" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                </div>
                <div>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full">Add Bed</button>
                </div>
            </div>
        </form>

        <!-- Beds List -->
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bed No</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rent</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($room->beds as $bed)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $bed->bed_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹{{ number_format($bed->monthly_rent, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <form action="{{ route('admin.beds.update-status', $bed) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <select name="status" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="vacant" {{ $bed->status == 'vacant' ? 'selected' : '' }}>Vacant</option>
                                    <option value="occupied" {{ $bed->status == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                    <option value="reserved" {{ $bed->status == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                    <option value="maintenance" {{ $bed->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <form action="{{ route('admin.beds.destroy', $bed) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this bed?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection