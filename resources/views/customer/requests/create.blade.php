@extends('layouts.customer')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-teal-50 via-cyan-50 to-violet-50 py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-display font-bold text-gray-900 mb-8">New Request</h1>

            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8">
                <form method="POST" action="{{ route('customer.requests.store') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ type: '{{ old('type', 'service') }}' }">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Request Type *</label>
                        <select name="type" x-model="type" required class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Subject *</label>
                        <input type="text" name="subject" required value="{{ old('subject') }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                        @error('subject') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="type === 'vacation'">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Move-out Date *</label>
                        <input type="date" name="preferred_date" value="{{ old('preferred_date') }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-xs text-gray-500 mt-1">At least {{ setting('notice_period_days', 30) }} days' notice is required.</p>
                        @error('preferred_date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="type !== 'vacation'">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Preferred Date</label>
                        <input type="date" name="preferred_date" value="{{ old('preferred_date') }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4" class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Attachment</label>
                        <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-xl hover:from-teal-600 hover:to-cyan-600 transition shadow-lg">
                            Submit Request
                        </button>
                        <a href="{{ route('customer.requests.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50 transition">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
