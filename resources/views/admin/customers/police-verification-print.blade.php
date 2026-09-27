<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Verification Form - {{ $customer->name }} - {{ setting('hostel_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8 print:py-0 print:bg-white">
    <div class="max-w-2xl mx-auto no-print flex justify-between items-center mb-4 px-4">
        <a href="{{ $backUrl }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        <button onclick="window.print()" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 transition">
            Print Form
        </button>
    </div>

    <div class="max-w-2xl mx-auto bg-white shadow-lg print:shadow-none rounded-2xl print:rounded-none p-10 border border-gray-100">
        <div class="text-center border-b-2 border-gray-100 pb-6 mb-6">
            @if(setting('logo_path'))
                <img src="{{ \Illuminate\Support\Facades\Storage::url(setting('logo_path')) }}" class="h-10 mx-auto mb-2">
            @endif
            <h1 class="text-xl font-display font-bold text-gray-900">{{ setting('hostel_name') }}</h1>
            @if(setting('contact_address'))<p class="text-sm text-gray-500">{{ setting('contact_address') }}</p>@endif
            @if(setting('contact_phone'))<p class="text-sm text-gray-500">{{ setting('contact_phone') }}</p>@endif
            <p class="text-lg font-bold text-primary-600 mt-4 uppercase tracking-wide">Tenant Verification Form</p>
            <p class="text-xs text-gray-400">For submission to the local police station, as required for PG/hostel tenant registration.</p>
        </div>

        <div class="grid grid-cols-2 gap-x-6 gap-y-4 mb-6 text-sm">
            <div class="col-span-2 flex items-center gap-4">
                <img src="{{ $customer->safe_photo_url }}" class="w-20 h-20 rounded-lg object-cover border border-gray-200">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Tenant Name</p>
                    <p class="font-bold text-gray-900 text-base">{{ $customer->name }}</p>
                    <p class="text-xs text-gray-500">{{ $customer->customer_code }}</p>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Date of Birth</p>
                <p class="text-gray-900">{{ $customer->dob->format('d M, Y') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Contact Number</p>
                <p class="text-gray-900">{{ $customer->phone }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Guardian's Contact</p>
                <p class="text-gray-900">{{ $customer->guardian_phone }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Occupation / Institution</p>
                <p class="text-gray-900">{{ $customer->work_details ?? '—' }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-xs font-semibold text-gray-500 uppercase">Permanent Address</p>
                <p class="text-gray-900">{{ $customer->address }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-xs font-semibold text-gray-500 uppercase">Present Address (This PG)</p>
                <p class="text-gray-900">
                    @if($booking?->bed?->room?->branch)
                        {{ $booking->bed->room->branch->name }} ({{ $booking->bed->room->branch->address }}) — Room {{ $booking->bed->room->room_number }}, Bed {{ $booking->bed->bed_number }}
                    @else
                        {{ setting('hostel_name') }}
                    @endif
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">ID Proof Type</p>
                <p class="text-gray-900">{{ $customer->id_proof_type ?? 'Not recorded' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">ID Proof Number</p>
                <p class="text-gray-900">{{ $customer->id_proof_number ?? 'Not recorded' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Date of Occupancy</p>
                <p class="text-gray-900">{{ $booking?->check_in_date?->format('d M, Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Form Generated</p>
                <p class="text-gray-900">{{ now()->format('d M, Y') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6 mt-16 pt-6 border-t border-gray-100 text-sm">
            <div>
                <p class="border-t border-gray-400 pt-1 text-center text-gray-600">Signature of Tenant</p>
            </div>
            <div>
                <p class="border-t border-gray-400 pt-1 text-center text-gray-600">Signature of PG/Hostel Owner</p>
            </div>
        </div>

        <p class="text-xs text-gray-400 text-center border-t border-gray-100 pt-6 mt-6">
            Generated by {{ setting('hostel_name') }} management software. Please attach a photocopy of the ID proof before submission.
        </p>
    </div>
</body>
</html>
