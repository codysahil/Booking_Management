<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted - {{ setting('hostel_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-teal-50 via-cyan-50 to-violet-50 min-h-screen flex items-center justify-center py-10">
    <div class="max-w-md mx-auto px-4 text-center">
        <div class="rounded-2xl border border-gray-100 bg-white p-10 shadow-sm">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-teal-100 text-teal-600">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1 class="font-display text-xl font-bold text-gray-900">Application Received</h1>
            <p class="mt-2 text-sm text-gray-500">
                Thank you! {{ setting('hostel_name') }}'s team will review your details and reach out to confirm your stay.
            </p>
            @if(setting('contact_phone'))
                <p class="mt-4 text-sm text-gray-600">Questions? Call us at <span class="font-semibold text-gray-900">{{ setting('contact_phone') }}</span></p>
            @endif
        </div>
    </div>
</body>
</html>
