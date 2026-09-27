<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Apply to Stay - {{ setting('hostel_name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-teal-50 via-cyan-50 to-violet-50 min-h-screen py-10">
    <div class="max-w-2xl mx-auto px-4">
        <div class="text-center mb-8">
            @if(setting('logo_path'))
                <img src="{{ \Illuminate\Support\Facades\Storage::url(setting('logo_path')) }}" class="h-12 mx-auto mb-3">
            @endif
            <h1 class="font-display text-2xl font-bold text-gray-900">{{ setting('hostel_name') }}</h1>
            @if(setting('tagline'))<p class="text-sm text-gray-500 mt-1">{{ setting('tagline') }}</p>@endif
            <p class="mt-4 text-gray-600">Fill in your details below and our team will get in touch to confirm your stay.</p>
        </div>

        <form method="POST" action="{{ route('register.store', $tenant) }}" enctype="multipart/form-data" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            @if ($errors->any())
                <div class="rounded-xl border-2 border-rose-200 bg-rose-50 p-4">
                    <h3 class="font-bold text-rose-800 mb-2 text-sm">Please fix the following:</h3>
                    <ul class="list-disc list-inside text-rose-700 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-form.card title="Your Details">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </x-slot:icon>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-form.input name="name" label="Full Name" required />
                    <x-form.input name="dob" label="Date of Birth" type="date" required />
                    <x-form.input name="phone" label="Phone Number" type="tel" required pattern="[0-9]{10}" maxlength="10" />
                    <x-form.input name="guardian_phone" label="Guardian/Parent Phone" type="tel" required pattern="[0-9]{10}" maxlength="10" />
                    <x-form.input name="email" label="Email (optional)" type="email" />
                    <x-form.input name="preferred_move_in_date" label="Preferred Move-in Date (optional)" type="date" />
                    <div class="md:col-span-2">
                        <x-form.textarea name="address" label="Permanent Address" :rows="3" required />
                    </div>
                    <div class="md:col-span-2">
                        <x-form.textarea name="work_details" label="Work/Study Details (optional)" :rows="2" placeholder="e.g. Software Engineer at ABC Company, Student at XYZ College" />
                    </div>
                    @if($branches->isNotEmpty())
                        <div class="md:col-span-2">
                            <x-form.select name="branch_id" label="Preferred Branch (optional)">
                                <option value="">No preference</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </x-form.select>
                        </div>
                    @endif
                </div>
            </x-form.card>

            <x-form.card title="Documents (optional)" :delay="80" description="Speeds up your check-in — you can also bring these in person.">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </x-slot:icon>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-form.file-input name="photo" label="Your Photo" accept="image/*" hint="A clear passport-size photo (max 10MB)" />
                    <x-form.file-input name="id_proof" label="ID Proof" accept=".pdf,.jpg,.jpeg,.png" hint="Aadhaar/PAN/Driving Licence (max 10MB)" />
                </div>
            </x-form.card>

            <x-form.button label="Submit Application" loading-label="Submitting…" class="w-full justify-center" />
        </form>
    </div>
</body>
</html>
