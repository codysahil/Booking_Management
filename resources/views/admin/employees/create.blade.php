@extends('layouts.admin')

@section('header', 'Add New Employee')

@section('content')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data"
            class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <x-form.card title="Employee Details">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </x-slot:icon>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-form.input name="name" label="Full Name" required />
                    <x-form.select name="role" label="Role" required>
                        <option value="">Select role</option>
                        <option value="Manager">Manager</option>
                        <option value="Warden">Warden</option>
                        <option value="Cook">Cook</option>
                        <option value="Security">Security</option>
                        <option value="Cleaner">Cleaner</option>
                    </x-form.select>
                    <x-form.input name="phone" label="Phone Number" required />
                    <x-form.select name="branch_id" label="Assigned Branch" required>
                        <option value="">Select branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-form.select>
                    <div class="md:col-span-2">
                        <x-form.textarea name="address" label="Address" :rows="3" required />
                    </div>
                </div>
            </x-form.card>

            <x-form.card title="Documents" description="Optional — can be added later" :delay="80">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </x-slot:icon>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-form.file-input name="photo" label="Employee Photo" accept="image/*" />
                    <x-form.file-input name="proof" label="ID Proof" accept="image/*,.pdf" />
                </div>
            </x-form.card>

            <div class="flex justify-end gap-3">
                <x-form.link-button :href="route('admin.employees.index')">Cancel</x-form.link-button>
                <x-form.button label="Save Employee" loading-label="Saving…" />
            </div>
        </form>
    </div>
@endsection
