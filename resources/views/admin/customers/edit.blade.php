@extends('layouts.admin')

@section('header', 'Edit Customer')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.customers.show', $customer) }}"
                class="text-primary-600 hover:text-primary-700 font-medium">
                ← Back to Customer Details
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border-2 border-red-200 rounded-xl p-4 mb-6">
                <h3 class="text-red-800 font-bold mb-2">Please fix the following errors:</h3>
                <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.customers.update', $customer) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Personal Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="name" required value="{{ old('name', $customer->name) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                        <input type="date" name="dob" required value="{{ old('dob', $customer->dob->format('Y-m-d')) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                        <input type="tel" name="phone" required value="{{ old('phone', $customer->phone) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Guardian Phone *</label>
                        <input type="tel" name="guardian_phone" required
                            value="{{ old('guardian_phone', $customer->guardian_phone) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Work/College Details</label>
                        <textarea name="work_details" rows="2"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('work_details', $customer->work_details) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                        <textarea name="address" rows="3" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('address', $customer->address) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Documents</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Photo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>

                        @if($customer->photo_path)
                            <div class="mb-4">
                                <img src="{{ $customer->safe_photo_url }}" alt="Current Photo"
                                    class="w-full max-w-xs rounded-lg border-2 border-gray-200">
                                <p class="text-xs text-gray-500 mt-2">Current Photo</p>
                            </div>
                        @endif

                        <input type="file" name="photo" accept="image/*" id="photo-input"
                            class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-xs text-gray-500 mt-2">Upload new photo to replace (Max: 2MB)</p>

                        <div id="photo-preview" class="mt-4 hidden">
                            <img src="" alt="Preview" class="w-full max-w-xs rounded-lg border-2 border-primary-200">
                            <p class="text-xs text-primary-600 mt-2">New Photo Preview</p>
                        </div>
                    </div>

                    <!-- ID Proof -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Proof</label>

                        @if($customer->id_proof_path)
                            <div class="mb-4">
                                @if(str_ends_with($customer->id_proof_path, '.pdf'))
                                    <a href="{{ $customer->safe_id_proof_url }}" target="_blank"
                                        class="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z">
                                            </path>
                                        </svg>
                                        View Current PDF
                                    </a>
                                @else
                                    <img src="{{ $customer->safe_id_proof_url }}" alt="Current ID Proof"
                                        class="w-full max-w-xs rounded-lg border-2 border-gray-200">
                                @endif
                                <p class="text-xs text-gray-500 mt-2">Current ID Proof</p>
                            </div>
                        @endif

                        <input type="file" name="id_proof" accept="image/*,.pdf" id="proof-input"
                            class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-xs text-gray-500 mt-2">Upload new document to replace (Max: 2MB)</p>

                        <div id="proof-preview" class="mt-4 hidden">
                            <img src="" alt="Preview" class="w-full max-w-xs rounded-lg border-2 border-primary-200">
                            <p class="text-xs text-primary-600 mt-2">New Document Preview</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.customers.show', $customer) }}"
                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500 text-white rounded-lg font-bold hover:from-rose-600 hover:via-pink-600 hover:to-purple-600 transition shadow-lg">
                    Update Customer
                </button>
            </div>
        </form>
    </div>

    <script>
        // Photo preview
        document.getElementById('photo-input').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('photo-preview');
                    preview.querySelector('img').src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        // ID Proof preview
        document.getElementById('proof-input').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('proof-preview');
                    preview.querySelector('img').src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else if (file && file.type === 'application/pdf') {
                const preview = document.getElementById('proof-preview');
                preview.innerHTML = '<p class="text-sm text-gray-600">PDF file selected: ' + file.name + '</p>';
                preview.classList.remove('hidden');
            }
        });
    </script>
@endsection