@extends('layouts.admin')

@section('header', 'Employee Details')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('admin.employees.index') }}" class="text-gray-500 hover:text-gray-700 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back to Employees
            </a>
            <a href="{{ route('admin.employees.edit', $employee) }}" 
                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Employee
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-8">
            <div class="md:flex">
                <div class="md:w-1/3 bg-gray-50 p-8 border-r border-gray-100 flex flex-col items-center text-center">
                    <div class="relative mb-4">
                        @if($employee->photo_path)
                            <img class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md"
                                src="{{ Storage::url($employee->photo_path) }}" alt="{{ $employee->name }}">
                        @else
                            <div
                                class="w-32 h-32 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-4xl border-4 border-white shadow-md">
                                {{ substr($employee->name, 0, 1) }}
                            </div>
                        @endif
                        <span
                            class="absolute bottom-1 right-1 bg-green-500 w-5 h-5 rounded-full border-2 border-white"></span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $employee->name }}</h2>
                    <p class="text-gray-500 font-medium">{{ $employee->employee_code }}</p>
                    <span class="mt-3 inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $employee->role }}
                    </span>

                    <div class="mt-6 w-full space-y-3">
                        <div class="flex items-center text-sm text-gray-600 justify-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                            {{ $employee->phone }}
                        </div>
                    </div>

                    <div class="mt-8 w-full">

                    </div>
                </div>

                <div class="md:w-2/3 p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Work Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</label>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $employee->branch->name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Role</label>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $employee->role }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Joined Date</label>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $employee->created_at->format('d M, Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Branch Address</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $employee->branch->address }}</p>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $employee->phone }}</p>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Address</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $employee->address }}</p>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Documents</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">ID Proof</label>
                            @if($employee->proof_path)
                                <a href="{{ Storage::url($employee->proof_path) }}" target="_blank"
                                    class="mt-2 inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                    <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                    View Document
                                </a>
                            @else
                                <span class="text-gray-400 text-sm">Not Uploaded</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
