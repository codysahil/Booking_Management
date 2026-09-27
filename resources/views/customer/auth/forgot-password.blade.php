<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - {{ setting('hostel_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-teal-50 via-cyan-50 to-violet-50 min-h-screen">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-full mb-4">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-display font-bold text-gray-900">Forgot Password?</h2>
                <p class="mt-2 text-gray-600">We'll email you a reset link</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border-2 border-gray-100 p-8">
                @if (session('status'))
                    <div class="mb-6 bg-green-50 border-2 border-green-200 rounded-xl p-4">
                        <p class="text-green-800 text-sm">{{ session('status') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-2 border-red-200 rounded-xl p-4">
                        <p class="text-sm font-medium text-red-800">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.password.email') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Registered Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            placeholder="you@example.com"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-lg">
                        <p class="mt-2 text-xs text-gray-500">The email your hostel has on file for you. No email on file? Contact the hostel directly to reset your password.</p>
                    </div>

                    <button type="submit"
                        class="w-full bg-gradient-to-r from-teal-500 via-cyan-500 to-violet-500 text-white py-4 px-6 rounded-xl font-bold text-lg hover:from-teal-600 hover:via-cyan-600 hover:to-violet-600 transition shadow-lg hover:shadow-xl">
                        Send Reset Link
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ route('customer.login') }}" class="text-sm text-gray-600 hover:text-gray-900 transition">
                        ← Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
