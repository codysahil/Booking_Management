@extends('layouts.admin')

@section('header', 'Add New Room')

@section('content')
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
        <form action="{{ route('admin.rooms.store') }}" method="POST">
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

            <div class="flex justify-end">
                <a href="{{ route('admin.rooms.index') }}"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded mr-2 hover:bg-gray-300">Cancel</a>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Save
                    Room</button>
            </div>
        </form>
    </div>
@endsection