<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $payment->receipt_number ?? $payment->id }} - {{ setting('hostel_name') }}</title>
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
            Print Receipt
        </button>
    </div>

    <div class="max-w-2xl mx-auto bg-white shadow-lg print:shadow-none rounded-2xl print:rounded-none p-10 border border-gray-100">
        <div class="flex justify-between items-start border-b-2 border-gray-100 pb-6 mb-6">
            <div>
                @if(setting('logo_path'))
                    <img src="{{ \Illuminate\Support\Facades\Storage::url(setting('logo_path')) }}" class="h-10 mb-2">
                @endif
                <h1 class="text-2xl font-display font-bold text-gray-900">{{ setting('hostel_name') }}</h1>
                @if(setting('contact_address'))<p class="text-sm text-gray-500">{{ setting('contact_address') }}</p>@endif
                @if(setting('contact_phone'))<p class="text-sm text-gray-500">{{ setting('contact_phone') }}</p>@endif
                @if(setting('gstin'))<p class="text-sm text-gray-500">GSTIN: {{ setting('gstin') }}</p>@endif
            </div>
            <div class="text-right">
                <p class="text-lg font-bold text-primary-600">RECEIPT</p>
                <p class="text-sm text-gray-500">{{ $payment->receipt_number ?? '#'.$payment->id }}</p>
                <p class="text-sm text-gray-500">{{ ($payment->paid_at ?? $payment->created_at)->format('d M, Y h:i A') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Received From</p>
                <p class="text-sm font-bold text-gray-900">{{ $payment->customer->name }}</p>
                <p class="text-sm text-gray-600">{{ $payment->customer->customer_code }}</p>
                <p class="text-sm text-gray-600">{{ $payment->customer->phone }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Payment Method</p>
                <p class="text-sm font-bold text-gray-900">{{ $payment->payment_method }}</p>
                @if($payment->razorpay_payment_id)
                    <p class="text-xs text-gray-500">Txn: {{ $payment->razorpay_payment_id }}</p>
                @elseif($payment->transaction_ref)
                    <p class="text-xs text-gray-500">Ref: {{ $payment->transaction_ref }}</p>
                @endif
                @if($payment->booking?->bed?->room?->branch)
                    <p class="text-xs text-gray-500 mt-1">{{ $payment->booking->bed->room->branch->name }}</p>
                @endif
            </div>
        </div>

        <table class="w-full mb-6">
            <thead>
                <tr class="border-b-2 border-gray-100">
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase py-2">Description</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase py-2">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payment->lineItems() as $item)
                    <tr class="border-b border-gray-50">
                        <td class="py-2 text-sm text-gray-800">{{ $item['description'] }}</td>
                        <td class="py-2 text-sm text-gray-800 text-right">{{ money($item['amount']) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="pt-4 text-base font-bold text-gray-900">Total Paid</td>
                    <td class="pt-4 text-base font-bold text-primary-600 text-right">{{ money($payment->amount) }}</td>
                </tr>
            </tfoot>
        </table>

        <p class="text-xs text-gray-400 text-center border-t border-gray-100 pt-6">
            {{ setting('receipt_footer') }}
        </p>
    </div>
</body>
</html>
