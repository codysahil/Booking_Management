<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - {{ setting('hostel_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-teal-50 via-cyan-50 to-violet-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-display font-bold text-gray-900">Reset Password</h1>
                <p class="text-gray-500 mt-2">Choose a new password for your account</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-2 border-red-200 rounded-xl p-4">
                    <p class="text-red-800 text-sm">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', request('email')) }}" required autofocus
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-teal-500 focus:ring-0 transition-colors"
                        placeholder="you@example.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-teal-500 focus:ring-0 transition-colors"
                        placeholder="••••••••">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-teal-500 focus:ring-0 transition-colors"
                        placeholder="••••••••">
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-teal-500 via-cyan-500 to-violet-500 text-white py-3 px-6 rounded-xl font-bold hover:from-teal-600 hover:via-cyan-600 hover:to-violet-600 transition-all duration-300 shadow-lg hover:shadow-xl">
                    Reset Password
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('admin.login') }}" class="text-sm text-gray-600 hover:text-gray-900 transition">
                    ← Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
