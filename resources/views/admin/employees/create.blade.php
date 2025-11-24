@extends('layouts.admin')

@section('header', 'Add New Employee')

@section('content')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">Employee Details</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" required
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="role" required
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- Select Role --</option>
                            <option value="Manager">Manager</option>
                            <option value="Warden">Warden</option>
                            <option value="Cook">Cook</option>
                            <option value="Security">Security</option>
                            <option value="Cleaner">Cleaner</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" required
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Branch</label>
                        <select name="branch_id" required
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- Select Branch --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="address" rows="3" required
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"></textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">Documents</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee Photo</label>
                        <input type="file" name="photo" accept="image/*" required
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ID Proof</label>
                        <input type="file" name="proof" required
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('admin.employees.index') }}"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 mr-3">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Save Employee
                </button>
            </div>
        </form>
    </div>
@endsection