@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Generate Monthly Charges</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-600 mb-6">
            This will generate monthly charges for all active customers. Rent amount will be taken from their bed's monthly rent.
            You can add EB and other charges later by editing individual charges.
        </p>

        <form method="POST" action="{{ route('admin.charges.generate') }}">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Month</label>
                <input type="month" name="month" value="{{ $month }}" required
                    class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                <p class="text-sm text-gray-500 mt-1">Charges will be generated for this month</p>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-800">Note:</p>
                        <p class="text-sm text-yellow-700">If charges already exist for this month, they will be skipped.</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 font-medium">
                    Generate Charges
                </button>
                <a href="{{ route('admin.charges.index') }}" 
                    class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 font-medium text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
