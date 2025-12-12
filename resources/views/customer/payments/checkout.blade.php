@extends('layouts.customer')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Complete Payment</h1>

            <!-- Payment Summary -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-bold text-gray-900 mb-3">Payment Details</h3>
                <div class="space-y-2">
                    @foreach($items as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $item['description'] }}</span>
                        <span class="font-medium text-gray-900">₹{{ number_format($item['amount']) }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="border-t border-gray-200 mt-3 pt-3 flex justify-between">
                    <span class="font-bold text-gray-900">Total Amount:</span>
                    <span class="text-2xl font-bold text-primary-600">₹{{ number_format($totalAmount) }}</span>
                </div>
            </div>

            <button id="rzp-button" class="w-full bg-primary-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-primary-700 transition">
                Pay ₹{{ number_format($totalAmount) }}
            </button>

            <p class="text-sm text-gray-500 mt-4 text-center">
                Secure payment powered by <strong>Razorpay</strong>
            </p>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('rzp-button').onclick = function(e) {
    e.preventDefault();
    
    var button = this;
    button.disabled = true;
    button.innerHTML = 'Processing...';
    
    var options = {
        "key": "{{ $razorpayKey }}",
        "amount": "{{ $order['amount'] }}",
        "currency": "{{ $order['currency'] }}",
        "name": "Honeybees Hostel",
        "description": "Monthly Charges Payment",
        "order_id": "{{ $order['id'] }}",
        "handler": function (response) {
            // Show processing message
            button.innerHTML = 'Verifying Payment...';
            
            // Create form and submit
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('customer.payments.verify') }}';
            
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            var orderIdInput = document.createElement('input');
            orderIdInput.type = 'hidden';
            orderIdInput.name = 'razorpay_order_id';
            orderIdInput.value = response.razorpay_order_id;
            form.appendChild(orderIdInput);
            
            var paymentIdInput = document.createElement('input');
            paymentIdInput.type = 'hidden';
            paymentIdInput.name = 'razorpay_payment_id';
            paymentIdInput.value = response.razorpay_payment_id;
            form.appendChild(paymentIdInput);
            
            var signatureInput = document.createElement('input');
            signatureInput.type = 'hidden';
            signatureInput.name = 'razorpay_signature';
            signatureInput.value = response.razorpay_signature;
            form.appendChild(signatureInput);
            
            document.body.appendChild(form);
            form.submit();
        },
        "prefill": {
            "name": "{{ $customer->name }}",
            "email": "{{ $customer->email }}",
            "contact": "{{ $customer->phone }}"
        },
        "theme": {
            "color": "#E91E63"
        },
        "modal": {
            "ondismiss": function() {
                button.disabled = false;
                button.innerHTML = 'Pay ₹{{ number_format($totalAmount) }}';
            },
            "escape": false,
            "backdropclose": false
        },
        "retry": {
            "enabled": true,
            "max_count": 3
        }
    };
    
    var rzp = new Razorpay(options);
    
    // Handle payment failure
    rzp.on('payment.failed', function (response) {
        console.error('Payment failed:', response.error);
        
        // Log the error details
        var errorMessage = response.error.description || 'Payment failed. Please try again.';
        var errorCode = response.error.code || 'UNKNOWN';
        
        // Redirect to failed page with error info
        window.location.href = '{{ route('customer.payments.failed') }}?error=' + encodeURIComponent(errorMessage) + '&code=' + errorCode;
    });
    
    rzp.open();
};

// Handle page unload during payment
window.addEventListener('beforeunload', function(e) {
    var button = document.getElementById('rzp-button');
    if (button && button.disabled) {
        e.preventDefault();
        e.returnValue = 'Payment is in progress. Are you sure you want to leave?';
        return e.returnValue;
    }
});
</script>
@endsection
