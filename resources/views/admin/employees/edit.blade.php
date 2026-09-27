@extends('layouts.admin')

@section('header', 'Edit Employee')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.employees.show', $employee) }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Employee Details
        </a>
    </div>

    @if ($errors->any())
        <div class="form-card-enter form-field-error mb-6 rounded-xl border-2 border-rose-200 bg-rose-50 p-4">
            <h3 class="font-bold text-rose-800 mb-2 text-sm">Please fix the following errors:</h3>
            <ul class="list-disc list-inside text-rose-700 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.employees.update', $employee) }}" method="POST" enctype="multipart/form-data"
        class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        @method('PUT')

        <x-form.card title="Personal Information">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </x-slot:icon>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-form.input name="name" label="Full Name" required :value="$employee->name" />
                <x-form.select name="role" label="Role" required>
                    <option value="">Select role</option>
                    @foreach(['Manager', 'Warden', 'Cook', 'Security', 'Cleaner'] as $role)
                        <option value="{{ $role }}" {{ old('role', $employee->role) == $role ? 'selected' : '' }}>{{ $role }}</option>
                    @endforeach
                </x-form.select>
                <x-form.input name="phone" label="Phone Number" type="tel" required :value="$employee->phone" />
                <x-form.select name="branch_id" label="Assigned Branch" required>
                    <option value="">Select branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $employee->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </x-form.select>
                <div class="md:col-span-2">
                    <x-form.textarea name="address" label="Address" :rows="3" required :value="$employee->address" />
                </div>
            </div>
        </x-form.card>

        <x-form.card title="Documents" :delay="80">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </x-slot:icon>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    @if($employee->photo_path)
                        <div class="mb-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Current Photo</p>
                            <img src="{{ Storage::url($employee->photo_path) }}" alt="Current Photo"
                                class="w-full max-w-[10rem] rounded-xl border-2 border-gray-200 shadow-sm">
                        </div>
                    @endif
                    <x-form.file-input name="photo" label="Replace Photo" accept="image/*" hint="Max 2MB" />
                </div>

                <div>
                    @if($employee->proof_path)
                        <div class="mb-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Current ID Proof</p>
                            @if(str_ends_with($employee->proof_path, '.pdf'))
                                <a href="{{ Storage::url($employee->proof_path) }}" target="_blank"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-700 rounded-xl text-sm font-medium hover:bg-rose-100 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path></svg>
                                    View Current PDF
                                </a>
                            @else
                                <img src="{{ Storage::url($employee->proof_path) }}" alt="Current ID Proof"
                                    class="w-full max-w-[10rem] rounded-xl border-2 border-gray-200 shadow-sm">
                            @endif
                        </div>
                    @endif
                    <x-form.file-input name="proof" label="Replace ID Proof" accept="image/*,.pdf" hint="Max 2MB" />
                </div>
            </div>
        </x-form.card>

        <div class="flex justify-end gap-3">
            <x-form.link-button :href="route('admin.employees.show', $employee)">Cancel</x-form.link-button>
            <x-form.button label="Update Employee" loading-label="Saving…" />
        </div>
    </form>
</div>
@endsection
